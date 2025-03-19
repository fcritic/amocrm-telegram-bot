<?php

declare(strict_types=1);

namespace App\Helpers\Telegram;

use Arhitector\Yandex\Disk;
use Core\Enum\ResponseMessage;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use RuntimeException;
use Telegram\Helper\ResponseHelper;
use function fclose;
use function file_get_contents;
use function fwrite;
use function stream_get_meta_data;
use function tmpfile;

/**
 * RequestHelper class
 */
class RequestHelper
{
 //    /**
//     * @throws TelegramException
//     */
//    public static function sendAndSave(string $token, array $date): array
//    {
//        $bot    = new Telegram($token);
//        $result = Request::sendMessage([
//            'chat_id' => '',
//            'text'    => $date['message']['message']['text'],
//        ]);
//        return [];
//    }

    /**
     * @param Telegram $telegram        Объект Telegram бота
     * @param int      $userId          ID пользователя на стороне Telegram
     * @return JsonResponse|string|null Возвращает ссылку на аватар из Яндекс диска
     */
    public static function getPhotoUrl(Telegram $telegram, int $userId): JsonResponse|null|string
    {
        // Запрашиваем фотографии профиля пользователя
        $userProfilePhotos = Request::getUserProfilePhotos(['user_id' => $userId]);

        // Проверяем наличие фотографий и выводим URL первой
        if ($userProfilePhotos->isOk() && $userProfilePhotos->getResult()->getTotalCount() > 0) {
            $fileId       = $userProfilePhotos->getResult()->getPhotos()[0][0]->getFileId();
            $fileResponse = Request::getFile(['file_id' => $fileId]);

            if ($fileResponse->isOk()) {
                $urlPhoto = "https://api.telegram.org/file/bot"
                    . $telegram->getApiKey()
                    . "/"
                    . $fileResponse->getResult()->getFilePath();

                try {
                    $disk     = new Disk($_ENV['Y_TOKEN']);
                    $tempFile = tmpfile();

                    if (! $tempFile) {
                        return ResponseHelper::getJsonResponse(message: ResponseMessage::FAIL_CREATE_FILE);
                    }

                    // Получаем содержимое файла по URL
                    $fileContent = file_get_contents($urlPhoto);
                    if ($fileContent === false) {
                        return ResponseHelper::getJsonResponse(message: ResponseMessage::NO_CONTENT);
                    }

                    // Записываем содержимое во временный файл
                    fwrite($tempFile, $fileContent);
                    // Получаем путь к временному файлу
                    $tempFilePath = stream_get_meta_data($tempFile)['uri'];

                    if (! $disk->getResource('app-amoCRM/avatar')->has()) {
                        $disk->getResource('app-amoCRM')->create();
                        $disk->getResource('app-amoCRM/avatar')->create();
                    }

                    $resource = $disk->getResource('app-amoCRM/avatar/' . $userId . '.jpg');
                    $resource->upload($tempFilePath, true, true);
                    // Закрываем временный файл
                    fclose($tempFile);
                } catch (Exception $e) {
                    throw new RuntimeException($e->getMessage());
                }
                return $resource->getLink();
            }
            return null;
        }
        return null;
    }
}
