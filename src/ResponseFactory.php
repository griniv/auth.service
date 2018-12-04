<?php
declare(strict_types=1);

namespace AuthService;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

trait ResponseFactory
{
    protected function createResponse($data, int $status = 200): ResponseInterface
    {
        $response = (new Response())
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
        $response
            ->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES));

        return $response;
    }
}