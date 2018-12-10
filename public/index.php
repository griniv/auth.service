<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Relay\Relay;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

require_once __DIR__ . '/../vendor/autoload.php';

(function () {
    /** @noinspection PhpUnhandledExceptionInspection */
    $container = (new ContainerBuilder())
        ->addDefinitions(__DIR__ . '/../config/definitions.php')
        ->build();

    $request = ServerRequestFactory::fromGlobals();
    /** @noinspection PhpUnhandledExceptionInspection */
    $response = $container->get(Relay::class)->handle($request);

    (new SapiEmitter())->emit($response);
})();