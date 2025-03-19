<?php

declare(strict_types=1);

namespace AmoCRM\Service\Factory;

use AmoJo\Models\Channel;
use Doctrine\DBAL\ConnectionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class AmoJoChannelFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): Channel
    {
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }

        return new Channel(
            uid: $config['amocrm']['amojo']['channel']['uid'],
            secretKey: $config['amocrm']['amojo']['channel']['secret_key']
        );
    }
}
