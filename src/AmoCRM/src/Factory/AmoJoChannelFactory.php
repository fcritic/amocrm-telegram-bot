<?php

declare(strict_types=1);

namespace AmoCRM\Factory;

use AmoJo\Exception\AmoJoException;
use AmoJo\Models\Channel;
use Doctrine\DBAL\ConnectionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

readonly class AmoJoChannelFactory
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

        $channelUid = $config['amojo']['channel_uid'];
        $secretKey = $config['amojo']['secret_key'];

        if (! isset($channelUid, $secretKey)) {
            throw new AmoJoException('Please set amojo.channel_uid and amojo.secret_key in config');
        }

        return new Channel(
            uid: $channelUid,
            secretKey: $secretKey
        );
    }
}
