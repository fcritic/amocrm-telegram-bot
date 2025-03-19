<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Database\BootstrapperInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class DatabaseInitMiddleware implements MiddlewareInterface
{
    /**
     * @param BootstrapperInterface $bootstrapper
     */
    public function __construct(private BootstrapperInterface $bootstrapper)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->bootstrapper->bootstrap();
        return $handler->handle($request);
    }
}
