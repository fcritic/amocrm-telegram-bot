<?php

declare(strict_types=1);

namespace Telegram\Handler;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\FileService;

readonly class FileProxyHandler implements RequestHandlerInterface
{
    /**
     * @param FileService $fileService
     */
    public function __construct(protected FileService $fileService)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $fileId = $request->getAttribute('file_id');
            $schema = $request->getQueryParams()['with'] ?? null;

            if (! $fileId || ! $schema) {
                throw new InvalidArgumentException('Required get parameter "with" is missing');
            }

            $response = $this->fileService->getFile($fileId, $schema);
            $fileName = basename($response->getBody()->getMetadata('uri'));

            return new Response(
                $response->getBody(),
                $response->getStatusCode(),
                array_merge($response->getHeaders(), [
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                ])
            );
        } catch (Exception $e) {
            return new \App\Helper\Response($e->getMessage());
        }
    }
}
