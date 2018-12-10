<?php

declare(strict_types = 1);

namespace AuthService\Controller;

use AuthService\AmoClient;
use AuthService\RedisFactory;
use AuthService\Controller\Error\BadRequest;
use Noodlehaus\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Сервис авторизации пользователей amoCRM по одноразовому токену (реализация тестового задания)
 *
 * @link   https://docs.google.com/document/d/1bjRlWxsvqLg77sSVxCa6xjU2W5VNJb46HJZIDVPHXJQ/edit
 * @author Anton Griniv <a.griniv@gmail.com>
 */
class AuthController extends AbstractController {

    /**
     * @var RedisFactory
     */
    private $redisFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AmoClient 
     */
    private $amoClient;

    /**
     * @param RedisFactory  $redisFactory
     * @param Config $config
     * @param AmoClient $amoClient
     */
    public function __construct(RedisFactory $redisFactory, Config $config, AmoClient $amoClient) {
        $this->redisFactory = $redisFactory;
        $this->config = $config;
        $this->amoClient = $amoClient;
    }

    /**
     * Обработка запроса на получение токена
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getToken(ServerRequestInterface $request): ResponseInterface {
        $config = $this->config;
        $domain = $config->get('amocrm.domain');
        $origin = $config->get('amocrm.scheme') . '://' . $domain . '.' . $config->get('amocrm.base');
        $this->setHeader('Access-Control-Allow-Origin', $origin);
        $this->setRules($request, [
            'login' => ['ParsedBody', '/^[_a-z0-9-\.]+@[_a-z0-9-\.]+\.[a-z]{2,}$/i'],
            'api_key' => ['ParsedBody', '/^[a-f0-9]{40}$/']
        ]);

        $this->amoClient->auth($domain, $this->getParam('login'), $this->getParam('api_key'));
        $user = $this->amoClient->getCurrentUser();

        $token = $this->generateToken();
        $redis = $this->redisFactory->create();
        $redis->set($token, json_encode($user), $config->get('service.tokenTTL'));

        return $this->createResponse(['token' => $token]);
    }

    /**
     * Обработка запроса на обмен токена
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws BadRequest
     */
    public function tradeToken(ServerRequestInterface $request): ResponseInterface {
        $this->setRules($request, [
            'token' => ['Attributes', '/^[a-f0-9]{' . $this->config->get('service.tokenLength') . '}$/']
        ]);
        $token = $this->getParam('token');

        $redis = $this->redisFactory->create();
        $user = $redis->get($token);
        if ($user === false) {
            throw new BadRequest('Invalid or expired token');
        }
        $redis->del($token);

        return $this->createResponse(json_decode($user, true));
    }

    /**
     * Генерация токена
     *
     * @return string
     */
    private function generateToken(): string {
        return bin2hex(random_bytes($this->config->get('service.tokenLength') / 2));
    }

}
