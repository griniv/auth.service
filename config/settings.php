<?php

use AuthService\Config;

Config::setStorage([
	'amocrm.domain' => 'example',
	'redis.host' => 'localhost',
	'redis.port' => 6379,
	'service.tokenLength' => 64,
	'service.tokenTTL' => 15 
]);
