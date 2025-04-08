<?php

declare(strict_types=1);

namespace AmoCRM;

use AmoJo\Middleware\MiddlewareInterface;
use AmoJo\Models\Channel;
use Dot\DependencyInjection\Attribute\Inject;

/**
 * Конфиг для AmoJo клиента. Требуется для выполнения запросов к сервису чатов amoCRM
 *
 * Большинство параметров находится в массиве канала чатов который выдают при регистрации канала чатов
 */
class AmoJoConfig
{
    /** @var string секретный ключ канала чатов */
    public string $secretKey;

    /** @var string ID канала чатов */
    public string $channelId;

    /** @var string code канала чатов */
    public string $originCode;

    /** @var array<class-string<MiddlewareInterface>> мидлвар для AmoJoClient*/
    public array $middleware;

    /** @var string сегмент ru|com */
    public string $segment;

    /**
     * @param array $config
     */
    #[Inject('config.amojo')]
    public function __construct(array $config)
    {
        $this->secretKey = $config['secret_key'];
        $this->channelId = $config['channel_uid'];
        $this->originCode = $config['channel_code'];
        $this->middleware = $config['middleware'];
        $this->segment = $config['segment'];
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return new Channel($this->channelId, $this->secretKey);
    }
}
