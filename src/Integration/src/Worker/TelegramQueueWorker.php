<?php

declare(strict_types=1);

namespace Integration\Worker;

use App\BeanstalkConfig;
use App\Worker\AbstractWorker;
use Integration\DTO\TelegramMessageData;
use Integration\Service\DatabaseService;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Vjik\TelegramBot\Api\Method\GetFile;
use Vjik\TelegramBot\Api\TelegramBotApi;
use Vjik\TelegramBot\Api\Type\Update\Update;

class TelegramQueueWorker extends AbstractWorker
{
    /** @var string Просматриваемая очередь */
    protected string $queue = 'telegram_queue';

    public function __construct(
        protected readonly BeanstalkConfig $beanstalk,
        protected readonly DatabaseService $databaseService,
    ) {
        parent::__construct($beanstalk);
    }

    /**
     * @throws \JsonException
     */
    public function process(mixed $data, OutputInterface $output): void
    {
        $output->writeln('Processing webhook: ' . date("Y-m-d H:i:s"));
        try {
            $dtoWebhook = Update::fromJson(json_encode($data['body'], JSON_THROW_ON_ERROR));

            $bot = new TelegramBotApi('7930934754:AAH0B1mATf4d1R_lWZBJgWZ3HH84BrKn62k');
            var_dump($bot->getUserProfilePhotos($dtoWebhook->message->from->id)->photos[0][2]->fileId);



            if ($dtoWebhook->message) {
                $output->writeln('Saving the event in the database');
//                $this->databaseService->saveDataMessage(dtoDb: new TelegramMessageData($dtoWebhook, $data['secret']));
            }
        } catch (Throwable $e) {
            $output->writeln('Error send message: ' . $e->getMessage());
        }

//        $bot = new TelegramBotApi('7323518386:AAGfw-mBUzi-mK_MOfCOiMreeQK7Ej4RauQ');
//        var_dump($dto->message->document->fileId);

//        $response = $bot->call(new GetFile($dto->message->document->fileId));
    }
}
