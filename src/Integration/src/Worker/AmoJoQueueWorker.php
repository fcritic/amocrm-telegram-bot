<?php

declare(strict_types=1);

namespace Integration\Worker;

use AmoJo\Client\AmoJoClient;
use AmoJo\Enum\DeliveryStatus;
use AmoJo\Enum\ErrorCode;
use AmoJo\Models\Deliver;
use AmoJo\Webhook\OutgoingMessageEvent;
use AmoJo\Webhook\ParserWebHooks;
use App\BeanstalkConfig;
use App\Worker\AbstractWorker;
use Integration\DTO\AmoJoMessageData;
use Integration\Service\DatabaseService;
use JsonException;
use Symfony\Component\Console\Output\OutputInterface;
use Telegram\Service\TelegramBotService;
use Throwable;

class AmoJoQueueWorker extends AbstractWorker
{
    /** @var string Просматриваемая очередь */
    protected string $queue = 'amojo_queue';

    public function __construct(
        protected readonly BeanstalkConfig $beanstalk,
        protected readonly ParserWebHooks $parserWebHook,
        protected readonly TelegramBotService $telegramService,
        protected readonly DatabaseService $databaseService,
        protected readonly AmoJoClient $amoJoClient,
    ) {
        parent::__construct($beanstalk);
    }

    /**
     * Получает из опереди полный хук и валидирут его по заголовку и общей модели, в данном случае message.
     * В случае если хук не прошел валидацию, то сохраняется в отдельной таблицы. Тут можно отправлять данный хук
     * в логи и отдавать исключение
     *
     * @throws JsonException
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
                $this->databaseService->saveDataMessage(dtoDb: new AmoJoMessageData($dto, $message));
            }
        } catch (Throwable $e) {
            $output->writeln('Error send message: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $this->amoJoClient->deliverStatus(
                $dto->getAccountUid(),
                $dto->getMessage()->getRefUid(),
                (new Deliver(DeliveryStatus::ERROR))
                    ->setMessageError($e->getMessage())
                    ->setErrorCode(ErrorCode::WITH_DESCRIPTION)
            );
        }
    }
}
