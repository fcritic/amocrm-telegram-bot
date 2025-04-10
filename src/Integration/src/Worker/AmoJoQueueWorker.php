<?php

declare(strict_types=1);

namespace Integration\Worker;

use AmoCRM\Service\AmoJoClientService;
use AmoJo\Enum\DeliveryStatus;
use AmoJo\Enum\ErrorCode;
use AmoJo\Webhook\OutgoingMessageEvent;
use AmoJo\Webhook\ParserWebHooks;
use App\BeanstalkConfig;
use App\Worker\AbstractWorker;
use Integration\DTO\AmoJoMessageData;
use Integration\Service\DatabaseService;
use Symfony\Component\Console\Output\OutputInterface;
use Telegram\Service\TelegramBotService;
use Throwable;

/**
 * Воркер для обработки событий из очереди amoJo.
 * Отвечает за:
 * - Парсинг входящих вебхуков
 * - Отправку событий в Telegram
 * - Сохранение данных в БД
 * - Обработку ошибок и обновление статусов доставки
 */
class AmoJoQueueWorker extends AbstractWorker
{
    /** @var string Имя очереди Beanstalk, которую обрабатывает воркер */
    protected string $queue = 'amojo_queue';

    public function __construct(
        protected readonly BeanstalkConfig $beanstalk,
        protected readonly ParserWebHooks $parserWebHook,
        protected readonly TelegramBotService $telegramService,
        protected readonly DatabaseService $databaseService,
        protected readonly AmoJoClientService $amoJoClientService,
    ) {
        parent::__construct($beanstalk);
    }

    /**
     * Основной метод обработки задачи из очереди:
     * 1. Парсит сырые данные вебхука в DTO
     * 2. Отправляет событие в Telegram
     * 3. Для исходящих сообщений сохраняет данные в БД
     * 4. Обрабатывает ошибки и обновляет статус доставки в amoJo в случае ошибки
     *
     * @param array $data Данные задачи из очереди:
     * - body: сырые данные вебхука
     * - scope_id: скоп по аккаунту
     * @param OutputInterface $output Интерфейс для вывода логов
     */
    public function process(array $data, OutputInterface $output): void
    {
        $dto = $this->parserWebHook->parse($data['body']);
        try {
            $output->writeln('Processing webhook: ' . date("Y-m-d H:i:s"));
            $output->writeln('Sending to telegram');

            $message = $this->telegramService->sendEventTelegram($dto);

            if ($dto instanceof OutgoingMessageEvent) {
                $output->writeln('Saving the event in the database');
                $this->databaseService->saveDataMessage(AmoJoMessageData::create([
                    'event' => $dto,
                    'message' => $message
                ]));
            }
        } catch (Throwable $e) {
            $output->writeln('Error send message: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->amoJoClientService->updateStatus(
                $dto->getAccountUid(),
                $dto->getMessage()->getRefUid(),
                DeliveryStatus::ERROR,
                $e->getMessage(),
                ErrorCode::WITH_DESCRIPTION
            );
        }
    }
}
