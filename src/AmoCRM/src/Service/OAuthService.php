<?php

declare(strict_types=1);

namespace AmoCRM\Service;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Factory\AmoCRMApiClientFactory;
use AmoCRM\Models\AccountModel;
use AmoCRM\OAuth\OAuthServiceInterface;
use AmoCRM\Repository\Interface\AccessTokenRepositoryInterface;
use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class OAuthService implements OAuthServiceInterface
{
    /** @var AmoCRMApiClient  */
    protected AmoCRMApiClient $client;

    public function __construct(
        protected readonly AccessTokenRepositoryInterface $accessTokenRepo,
        protected readonly AccountRepositoryInterface $accountRepo,
        protected readonly AmoCRMApiClientFactory $factoryClient,
    ) {
        $this->client = $this->factoryClient->make();
        $this->client->onAccessTokenRefresh(
            function (AccessTokenInterface $accessToken, string $baseDomain) {
                $this->saveOAuthToken($accessToken, $baseDomain);
            }
        );
    }

    /**
     * @param array<string, string> $params
     * @return void
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function process(array $params): void
    {
        $subDomain = $params['referer'];
        $accessToken = $this->getTokenFromAuthorizationCode($subDomain, $params['code']);

        $this->saveAccount();
        $this->saveOAuthToken($accessToken, $subDomain);
    }

    /**
     * @param string $subDomain
     * @param string $code
     * @return AccessTokenInterface
     * @throws AmoCRMApiException
     */
    protected function getTokenFromAuthorizationCode(string $subDomain, string $code): AccessTokenInterface
    {
        try {
            $this->client->setAccountBaseDomain(domain: $subDomain);

            /** @var AccessToken $accessToken */
            $accessToken = $this->client
                ->getOAuthClient()
                ->getAccessTokenByCode(code: $code);
            $this->client->setAccessToken($accessToken);

            return $accessToken;
        } catch (AmoCRMoAuthApiException $e) {
            throw new AmoCRMApiException($e->getMessage());
        }
    }

    /**
     * @param AccessTokenInterface $accessToken
     * @param string $baseDomain
     * @return void
     */
    public function saveOAuthToken(AccessTokenInterface $accessToken, string $baseDomain): void
    {
        /** @var array<string, int> $query */
        $query = $this->accountRepo->getFieldsId($baseDomain);
        $fieldTokenId = $query['field_access_token_id'];

        if ($fieldTokenId !== null) {
            $this->accessTokenRepo->updateAccessToken(
                id: $fieldTokenId,
                accessToken: $accessToken
            );
        } else {
            $this->accessTokenRepo->createAccessToken(
                accountId: $query['field_account_id'],
                accessToken: $accessToken
            );
        }
    }

    /**
     * @return void
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMMissedTokenException
     */
    protected function saveAccount(): void
    {
        $this->accountRepo->firstOrCreateAccount(
            subDomain: $this->client->getAccountBaseDomain(),
            amoAccountId: $this->client->account()->getCurrent()?->getId(),
            amoJoId: $this->client->account()->getCurrent(
                with: AccountModel::getAvailableWith()
            )?->getAmojoId()
        );
    }

    public function getClient(int $accountId): AmoCRMApiClient
    {
        $query = $this->accountRepo->getAccountAndTokens($accountId);

        $accessToken = $query?->getAttribute('accessToken')->first();
        $subDomain = $query?->sub_domain;

        $this->client->setAccessToken(new AccessToken([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->refresh_token,
            'expires' => $accessToken->expires,
        ]));

        return $this->client;
    }
}
