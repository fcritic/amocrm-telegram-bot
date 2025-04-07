<?php

declare(strict_types=1);

namespace Integration\Worker;

use AmoCRM\Service\AmoJoClientService;
use AmoCRM\Service\OAuthService;
use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\MessageResponse;
use AmoJo\Models\Interfaces\MessageInterface;
use App\BeanstalkConfig;
use App\Worker\AbstractWorker;
use Integration\DTO\TelegramMessageData;
use Integration\Service\DatabaseService;
use Symfony\Component\Console\Output\OutputInterface;
use Telegram\Service\TelegramFileService;
use Throwable;
use Vjik\TelegramBot\Api\Type\Update\Update;

class TelegramQueueWorker extends AbstractWorker
{
    /** @var string Просматриваемая очередь */
    protected string $queue = 'telegram_queue';

    public function __construct(
        protected readonly BeanstalkConfig $beanstalk,
        protected readonly DatabaseService $databaseService,
        protected readonly TelegramFileService $fileService,
        protected readonly AmoJoClient $amoJoClient,
        protected readonly OAuthService $oAuthService,
        protected readonly AmoJoClientService $amoJoService,
    ) {
        parent::__construct($beanstalk);
    }

    /**
     * @throws \JsonException
     */
    public function process(array $data, OutputInterface $output): void
    {
        $output->writeln('Processing webhook: ' . date("Y-m-d H:i:s"));
        try {
            $dtoWebhook = Update::fromJson(json_encode($data['body'], JSON_THROW_ON_ERROR));
            $webhookSecret = $data['webhook_secret'];
            $telegramUserId = match (true) {
                isset($dtoWebhook->message->from->id)         => $dtoWebhook->message->from->id,
                isset($dtoWebhook->messageReaction->user->id) => $dtoWebhook->messageReaction->user->id,
                isset($dtoWebhook->editedMessage->from->id)   => $dtoWebhook->editedMessage->from->id,
            };
            $fileId = $this->fileService->getAvatarFileId($telegramUserId, $webhookSecret);

            $messageDto = TelegramMessageData::create([
                'update' => $dtoWebhook,
                'file_id' => $fileId,
                'webhook_secret' => $webhookSecret,
            ]);

            /** @var MessageInterface $response */
            $response = $this->amoJoService->sendEventAmoJo($dtoWebhook, $messageDto);

            if ($response instanceof MessageResponse) {
                $output->writeln('Saving the event in the database');

                $dtoDb = $messageDto->withResponse($response);
                $this->databaseService->saveDataMessage($dtoDb);
            }
        } catch (Throwable $e) {
            $output->writeln('Error send message: '
                . PHP_EOL . $e->getMessage()
                . PHP_EOL . $e->getFile()
                . PHP_EOL . $e->getLine());
        }
    }
}
