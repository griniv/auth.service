<?php

declare(strict_types = 1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

use AuthService\AuthService;

use function DI\create;
use function DI\get;

return [
	ResponseInterface::class => create(Response::class),	
	AuthService::class => create()->constructor(get(ResponseInterface::class)),	
	'handleErrors' => function(ContainerInterface $c){		
		return [$c->get(AuthService::class), 'handleErrors'];
	},
	'getToken' => function(ContainerInterface $c){		
		return [$c->get(AuthService::class), 'getToken'];
	},
	'tradeToken' => function(ContainerInterface $c){		
		return [$c->get(AuthService::class), 'tradeToken'];
	}
];