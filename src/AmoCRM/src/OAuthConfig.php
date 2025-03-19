<?php

declare(strict_types=1);

namespace AmoCRM;

use AmoCRM\OAuth\OAuthConfigInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class OAuthConfig implements OAuthConfigInterface
{
    /** @var string */
    protected string $clientId;

    /** @var string */
    protected string $clientSecret;

    /** @var string */
    protected string $redirectUri;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        protected readonly ContainerInterface $container,
    ) {
        try {
            $this->clientId = $this->container->get('config')['amocrm']['client_id'];
            $this->clientSecret = $this->container->get('config')['amocrm']['client_secret'];
            $this->redirectUri = $this->container->get('config')['amocrm']['redirect_uri'];
        } catch (ContainerExceptionInterface $e) {
            exit('OAuthConfig ' . $e->getMessage());
        }
    }

    public function getIntegrationId(): string
    {
        return $this->clientId;
    }

    public function getSecretKey(): string
    {
        return $this->clientSecret;
    }

    public function getRedirectDomain(): string
    {
        return $this->redirectUri;
    }
}
