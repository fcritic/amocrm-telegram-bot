<?php

declare(strict_types=1);

namespace AmoCRM\Service;

use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Collections\SourcesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\SourceModel;
use AmoCRM\OAuth\OAuthServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Vjik\TelegramBot\Api\Type\User;

/**
 * Сервис-обертка для работы с API v4 amoCRM
 */
class AmoCrmClientService
{
    #[Inject(
        OAuthService::class,
        'config.amojo.channel_code'
    )]
    public function __construct(
        protected OAuthServiceInterface $oauthService,
        protected string $channelCode,
    ) {
    }

    /**
     * Тут логика такая, что у интеграции может быть только один источник.
     * TODO можно указывать ID воронки где именно создавать источник для отправки сделки в выбранную воронку
     *
     * @param int $accountId
     * @param string $subDomain
     * @param User $bot
     * @return BaseApiCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     */
    public function addSources(int $accountId, string $subDomain, User $bot): BaseApiCollection
    {
        $amoCRMClient = $this->oauthService->getClient($accountId);

        $amoCRMClient->setAccountBaseDomain(str_replace('https://', '', $subDomain));

        $sourcesCollection = new SourcesCollection();
        $source = (new SourceModel())
            ->setName($bot->firstName)
            ->setExternalId($bot->username)
            ->setOriginCode($this->channelCode);

        try {
            /**
             * Не проверяю на пустоту, поскольку в случае когда источников нет,
             * то мы ловим AmoCRMApiNoContentException
             */
            $sources = $amoCRMClient->sources()->get();
            $source->setId($sources?->current()?->getId());

            return $amoCRMClient->sources()->update($sourcesCollection->add($source));
        } catch (AmoCRMApiNoContentException $e) {
            return $amoCRMClient->sources()->add($sourcesCollection->add($source));
        }
    }
}
