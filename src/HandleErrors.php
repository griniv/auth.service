<?php
declare(strict_types=1);

namespace AuthService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class HandleErrors implements MiddlewareInterface
{
    use ResponseFactory;

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $response = $this->createResponse(['message' => $e->getMessage()], 500);
        }

        return $response;
    }
}