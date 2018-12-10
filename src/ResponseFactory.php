<?php

declare(strict_types = 1);

namespace AuthService;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

trait ResponseFactory {

    /**
     * @var array 
     */
    private $headers = [];

    /**
     * @param string $header
     * @param string $value
     */
    protected function setHeader(string $header, string $value): void {
        $this->headers[$header] = $value;
    }

    /**
     * @param mixed $data
     * @param int $status
     * @return ResponseInterface
     */
    protected function createResponse($data, int $status = 200): ResponseInterface {
        $response = (new Response())
                ->withStatus($status)
                ->withHeader('Content-Type', 'application/json');
        foreach ($this->headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }
        $response
                ->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES));

        return $response;
    }

}
