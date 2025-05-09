<?php

declare(strict_types=1);

namespace AmoCRM;

use AmoCRM\OAuth\OAuthConfigInterface;
use Dot\DependencyInjection\Attribute\Inject;

/**
 * Конфиг интеграции
 */
readonly class OAuthConfig implements OAuthConfigInterface
{
    /** @var string */
    protected string $clientId;

    /** @var string */
    protected string $clientSecret;

    /** @var string */
    protected string $redirectUri;

    /**
     * @param array $configAmoCRM
     */
    #[Inject('config.amocrm')]
    public function __construct(protected array $configAmoCRM)
    {
        $this->clientId = $configAmoCRM['client_id'];
        $this->clientSecret = $configAmoCRM['client_secret'];
        $this->redirectUri = $configAmoCRM['redirect_uri'];
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getRedirectDomain(): string
    {
        return $this->redirectUri;
    }
}
