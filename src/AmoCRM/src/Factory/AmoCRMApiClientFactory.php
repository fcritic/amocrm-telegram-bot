<?php

declare(strict_types=1);

namespace AmoCRM\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\OAuth\OAuthConfigInterface;

readonly class AmoCRMApiClientFactory
{
    /**
     * AmoCRMApiClientFactory constructor.
     *
     * @param OAuthConfigInterface  $oAuthConfig
     */
    public function __construct(protected OAuthConfigInterface $oAuthConfig)
    {
    }

    /**
     * @return AmoCRMApiClient
     */
    public function make(): AmoCRMApiClient
    {
        return new AmoCRMApiClient(
            $this->oAuthConfig->getIntegrationId(),
            $this->oAuthConfig->getSecretKey(),
            $this->oAuthConfig->getRedirectDomain()
        );
    }
}
