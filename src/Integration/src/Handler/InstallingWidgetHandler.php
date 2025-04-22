<?php

declare(strict_types=1);

namespace Integration\Handler;

use AmoCRM\Collections\SourcesCollection;
use AmoCRM\Models\DisposableTokenModel;
use AmoCRM\Models\SourceModel;
use AmoCRM\OAuth\OAuthServiceInterface;
use AmoCRM\Service\AmoJoClientService;
use AmoCRM\Service\OAuthService;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Dot\DependencyInjection\Attribute\Inject;
use Exception;
use Integration\Service\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramBotService;

/**
 * Хендлер для установки виджета в аккаунте. Получает запрос при сохранении настроек интеграции
 */
readonly class InstallingWidgetHandler implements RequestHandlerInterface
{
    #[Inject(
        TelegramBotService::class,
        DatabaseService::class,
        OAuthService::class,
        AmoJoClientService::class,
        'config.amojo.channel_code'
    )]
    public function __construct(
        protected TelegramBotService $botService,
        protected DatabaseService $dbService,
        protected OAuthServiceInterface $oauthService,
        protected AmoJoClientService $amoJoClientService,
        protected string $channelCode,
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var DisposableTokenModel $disposableToken */
        $disposableToken = $request->getAttribute('disposable_token');
        $token = $request->getParsedBody()['telegram_token'];
        $accountId = $disposableToken->getAccountId();

        try {
            if ($bot = $this->botService->setWebhook($token)) {
                $this->dbService->saveTelegramToken($token, $accountId, $bot->username);

                $amoCRMClient = $this->oauthService->getClient($accountId);

                $amoCRMClient->setAccountBaseDomain(str_replace(
                    'https://',
                    '',
                    $disposableToken->getAccountDomain()
                ));

                $sourcesCollection = new SourcesCollection();
                $source = new SourceModel();
                $source
                    ->setName($bot->firstName)
                    ->setOriginCode($this->channelCode)
                    ->setExternalId($bot->username);

                $sourcesCollection->add($source);
                $sourcesService = $amoCRMClient->sources();

                $sourcesService->add($sourcesCollection);

                $this->amoJoClientService->connectChannel($request->getParsedBody()['account_uid']);
            }
        } catch (Exception $e) {
            return new Response(
                ResponseMessage::CHECK_TOKEN,
                ResponseStatus::BAD_REQUEST,
                $e->getMessage()
            );
        }

        return new Response(
            message: ResponseMessage::SUCCESS,
            code: ResponseStatus::SUCCESS,
            data: [
                'external_id' => $bot->username,
                'name' => $bot->firstName,
            ]
        );
    }
}
