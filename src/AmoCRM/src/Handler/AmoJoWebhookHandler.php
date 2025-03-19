<?php

declare(strict_types=1);

namespace AmoCRM\Handler;

use AmoJo\Enum\WebHookType;
use AmoJo\Webhook\Traits\ValidationTrait;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Integration\Producer\AmoJoQueueProducer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;
use AmoJo\Exception\InvalidRequestWebHookException;
use AmoJo\Exception\UnsupportedMessageTypeException;

/**
 * Принимает вебхуки из amoCRM на события исходящего сообщения от пользователя
 */
readonly class AmoJoWebhookHandler implements RequestHandlerInterface
{
    use ValidationTrait;

    /**
     * @param AmoJoQueueProducer $producer
     */
    public function __construct(protected AmoJoQueueProducer $producer)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = (array) $request->getParsedBody();
            $type = match (true) {
                isset($body['message']) => WebHookType::MESSAGE,
                isset($body['action']['reaction']) => WebHookType::REACTION,
                isset($body['action']['typing']) => WebHookType::TYPING,
                default => throw new UnsupportedMessageTypeException()
            };

            $this->validateStructure(
                data: $body,
                requiredFields: $this->getValidationRules($type),
                errorPrefix: "[{$type}]"
            );

            $this->producer->produce(data: [
                'scope_id' => $request->getAttribute('scope_id'),
                'body' => $body,
            ]);
        } catch (UnsupportedMessageTypeException $e) {
            return new Response(ResponseMessage::INVALID_EVENT);
        } catch (InvalidRequestWebHookException $e) {
            return new Response($e->getMessage(), ResponseStatus::BAD_REQUEST);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }

        return new Response(ResponseMessage::SUCCESS, ResponseStatus::SUCCESS);
    }
}
