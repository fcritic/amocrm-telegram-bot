<?php

declare(strict_types=1);

namespace Integration\Middleware;

use AmoJo\Middleware\MiddlewareInterface;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use JsonException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class LoggerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private bool $logBody;
    private int $maxBodyLength;

    public function __construct(
        bool $logBody = true,
        int $maxBodyLength = 2000
    ) {
        $this->logger = new Logger('amoJo');
        $this->logBody = $logBody;
        $this->maxBodyLength = $maxBodyLength;
        $this->logger->pushHandler(new StreamHandler('/var/www/application/log/app.log'));
    }

    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $this->logRequest($request);
            $startTime = microtime(true);

            /** @var PromiseInterface $promise */
            $promise = $handler($request, $options);

            return $promise->then(
                $this->handleSuccess($request, $startTime),
                $this->handleFailure($request, $startTime)
            );
        };
    }

    private function handleSuccess(RequestInterface $request, float $startTime): callable
    {
        return function (ResponseInterface $response) use ($request, $startTime) {
            $this->logResponse($request, $response, $startTime);
            return $response;
        };
    }

    private function handleFailure(RequestInterface $request, float $startTime): callable
    {
        return function (Throwable $exception) use ($request, $startTime) {
            $this->logError($request, $exception, $startTime);
            throw $exception;
        };
    }

    private function logRequest(RequestInterface $request): void
    {
        $context = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $request->getHeaders(),
        ];

        if ($this->logBody) {
            $body = (string) $request->getBody();
            $context['body'] = $this->parseJsonBody($body);
        }

        $this->logger->info('HTTP Request', $context);
    }

    private function logResponse(
        RequestInterface $request,
        ResponseInterface $response,
        float $startTime
    ): void {
        $context = [
            'status' => $response->getStatusCode(),
            'duration' => round(microtime(true) - $startTime, 3),
            'request_method' => $request->getMethod(),
            'request_uri' => (string)$request->getUri(),
            'headers' => $response->getHeaders(),
        ];

        if ($this->logBody) {
            $body = (string) $response->getBody();
            $context['body'] = $this->parseJsonBody($body);
        }

        $this->logger->info('HTTP Response', $context);
    }

    private function logError(
        RequestInterface $request,
        Throwable $error,
        float $startTime
    ): void {
        $context = [
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'duration' => round(microtime(true) - $startTime, 3),
            'request_method' => $request->getMethod(),
            'request_uri' => (string)$request->getUri(),
            'trace' => $error->getTraceAsString(),
        ];

        $this->logger->error('HTTP Error', $context);
    }

    private function truncateBody(string $body): string
    {
        if (strlen($body) > $this->maxBodyLength) {
            return substr($body, 0, $this->maxBodyLength) . '... [TRUNCATED]';
        }
        return $body;
    }

    private function parseJsonBody(string $body): array
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            return ['json' => $decoded];
        } catch (JsonException $e) {
            return [
                'error' => 'Invalid JSON format',
                'raw' => $this->truncateBody($body)
            ];
        }
    }
}
