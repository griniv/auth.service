<?php
declare(strict_types=1);

namespace AuthService\Controller;

use AuthService\AmoClient;
use AuthService\Config;
use AuthService\Controller\Error\BadRequest;
use Exception;
use Redis;

/**
 * Сервис авторизации пользователей amoCRM по одноразовому токену (реализация тестового задания)
 *
 * @link   https://docs.google.com/document/d/1bjRlWxsvqLg77sSVxCa6xjU2W5VNJb46HJZIDVPHXJQ/edit
 * @author Anton Griniv <a.griniv@gmail.com>
 */
class AuthController extends AbstractController
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Redis  $redis
     * @param Config $config
     */
    public function __construct(Redis $redis, Config $config)
    {
        $this->redis = $redis;
        $this->config = $config;
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
        $amoClient = new AmoClient(Config::get('amocrm.domain'), $login, $apiKey);
        $user = $amoClient->getCurrentUser();

        $token = $this->generateToken();
        $this->redis->set($token, json_encode($user), $this->config::get('service.tokenTTL'));

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
        if (strlen($token) !== Config::get('service.tokenLength')) {
            throw new BadRequest('Invalid token length');
        }

        $user = $this->redis->get($token);
        if ($user === false) {
            throw new BadRequest('Invalid or expired token');
        }
        $this->redis->del($token);

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
        return bin2hex(random_bytes(Config::get('service.tokenLength') / 2));
    }
}
