<?php

declare(strict_types = 1);

use DI\ContainerBuilder;
use FastRoute\RouteCollector;
use Middlewares\Utils\CallableHandler;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use Relay\Relay;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

use function FastRoute\simpleDispatcher;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/settings.php';

// Создаем и конфигурируем DI-контейнер
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions(__DIR__ . '/../config/definitions.php');

$container = $containerBuilder->build();

// Конфигурируем стек middleware
$dispatcher = simpleDispatcher(function (RouteCollector $r){
	$r->post('/get_token', 'getToken');
	$r->get('/trade_token/{token:[a-f0-9]+}', 'tradeToken');
});

$middlewareQueue[] = new CallableHandler($container->get('handleErrors'));
$middlewareQueue[] = new FastRoute($dispatcher);
$middlewareQueue[] = new RequestHandler($container);

// Создаем диспетчер middleware и передаем обработку запроса 
$requestHandler = new Relay($middlewareQueue);
$response = $requestHandler->handle(ServerRequestFactory::fromGlobals());

// Возвращаем ответ
$emitter = new SapiEmitter();
return $emitter->emit($response);