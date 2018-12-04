<?php
declare(strict_types=1);

namespace AuthService\Controller;

use AuthService\AmoClient;
use AuthService\Config;
use AuthService\RedisFactory;
use AuthService\Controller\Error\BadRequest;
use Exception;

/**
 * Сервис авторизации пользователей amoCRM по одноразовому токену (реализация тестового задания)
 *
 * @link   https://docs.google.com/document/d/1bjRlWxsvqLg77sSVxCa6xjU2W5VNJb46HJZIDVPHXJQ/edit
 * @author Anton Griniv <a.griniv@gmail.com>
 */
class AuthController extends AbstractController
{
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
     * @param Redis  $redis
     * @param Config $config
     */
    public function __construct(RedisFactory $redisFactory, Config $config, AmoClient $amoClient)
    {
        $this->redisFactory = $redisFactory;
        $this->config = $config;
        $this->amoClient = $amoClient;
    }

    /**
     * Обработка запроса на получение токена
     *
     * @param string $login
     * @param string $apiKey
     *
     * @return array
     * @throws Exception
     */
    protected function getToken(string $login, string $apiKey): array
    {
        $this->amoClient->auth($this->config::get('amocrm.domain'), $login, $apiKey);
        $user = $this->amoClient->getCurrentUser();

        $token = $this->generateToken();
        $redis = $this->redisFactory->create();
        $redis->set($token, json_encode($user), $this->config::get('service.tokenTTL'));

        return ['token' => $token];
    }

    /**
     * Обработка запроса на обмен токена
     *
     * @param string $token
     *
     * @return array
     * @throws BadRequest
     */
    protected function tradeToken(string $token): array
    {
        if (strlen($token) !== $this->config::get('service.tokenLength')) {
            throw new BadRequest('Invalid token length');
        }

        $redis = $this->redisFactory->create();
        $user = $redis->get($token);
        if ($user === false) {
            throw new BadRequest('Invalid or expired token');
        }
        $redis->del($token);

        return json_decode($user, true);
    }

    /**
     * Генерация токена
     *
     * @return string
     * @throws Exception
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes($this->config::get('service.tokenLength') / 2));
    }
}
