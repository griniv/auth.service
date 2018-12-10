<?php

declare(strict_types = 1);

use AuthService\RedisFactory;
use AuthService\Controller\AuthController;
use AuthService\HandleErrors;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Middlewares\FastRoute;
use Middlewares\UrlEncodePayload;
use Middlewares\RequestHandler;
use Noodlehaus\Config;
use Noodlehaus\Parser\Yaml;
use Psr\Container\ContainerInterface;
use Relay\Relay;
use function FastRoute\simpleDispatcher;

return [
    FastRoute::class => function (Dispatcher $dispatcher) {
        return new FastRoute($dispatcher);
    },
    Dispatcher::class => function () {
        return simpleDispatcher(function (RouteCollector $r) {
            $r->post('/token', [AuthController::class, 'getToken']);
            $r->get('/token/{token:[a-f0-9]+}', [AuthController::class, 'tradeToken']);
        });
    },
    Relay::class => function (ContainerInterface $c) {
        return new Relay([
            $c->get(HandleErrors::class),
            $c->get(FastRoute::class),
            $c->get(UrlEncodePayload::class),
            $c->get(RequestHandler::class),
        ]);
    },
    RequestHandler::class => function (ContainerInterface $c) {
        return new RequestHandler($c);
    },
    Config::class => function(ContainerInterface $c) {
        return new Config(__DIR__ . '/settings.yaml', $c->get(Yaml::class));
    }
];
