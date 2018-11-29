<?php

declare(strict_types = 1);

namespace AuthService;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Redis;
use Throwable;
use Exception;

/**
 * Сервис авторизации пользователей amoCRM по одноразовому токену (реализация тестового задания)
 * @link https://docs.google.com/document/d/1bjRlWxsvqLg77sSVxCa6xjU2W5VNJb46HJZIDVPHXJQ/edit
 * @author Anton Griniv <a.griniv@gmail.com>
 */
class AuthService {

    /**     
     * @var ResponseInterface 
     */
    private $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response) {
        $this->response = $response;
    }

    /**
     * Обработка ошибок
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface     
     */
    public function handleErrors(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        try {
            $response = $handler->handle($request);
            if ($response->getStatusCode() !== 200)
                throw new Exception($response->getStatusCode() . ' ' . $response->getReasonPhrase());
        } catch (Throwable $e) {
            $response = $this->formatResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * Обработка запроса на получение токена
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getToken(ServerRequestInterface $request): ResponseInterface {
        $data = json_decode($request->getBody()->getContents(), true);
        $this->validateData($data);

        $amoClient = new AmoClient(Config::get('amocrm.domain'), $data['login'], $data['hash']);
        $user = $amoClient->getCurrentUser();

        $token = $this->generateToken();
        $redis = $this->redisConnect();
        $redis->set($token, json_encode($user));
        $redis->expire($token, Config::get('service.tokenTTL'));

        return $this->formatResponse(['status' => 'ok', 'token' => $token]);
    }
    
    /**
     * Обработка запроса на обмен токена
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function tradeToken(ServerRequestInterface $request): ResponseInterface {
        $token = $request->getAttribute('token');
        if (strlen($token) !== Config::get('service.tokenLength'))
            throw new Exception('Invalid token length');

        $redis = $this->redisConnect();
        if (!$json = $redis->get($token))
            throw new Exception('Invalid or expired token');
        $redis->del($token);
        $user = json_decode($json, true);

        return $this->formatResponse(['status' => 'ok', 'user' => $user]);
    }
    
    /**
     * Форматирование ответов
     * @param array $data
     * @return ResponseInterface
     */
    private function formatResponse(array $data): ResponseInterface {
        $response = $this->response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data));
        return $response;
    }
    
    /**
     * Валидация данных при запросе на получение токена
     * @param array $data
     * @throws Exception
     */
    private function validateData(array $data): void {
        if (is_null($data))
            throw new Exception('Invalid JSON');
        if (empty($data['login']) || !is_string($data['login']) || empty($data['hash']) || !is_string($data['hash']))
            throw new Exception('Bad request data');
    }

    /**
     * Генерация токена
     * @return string
     */
    private function generateToken(): string {
        return bin2hex(random_bytes(Config::get('service.tokenLength') / 2));
    }

    /**
     * Подключение к Redis
     * @return Redis
     */
    private function redisConnect(): Redis {
        $redis = new Redis();
        $redis->connect(Config::get('redis.host'), Config::get('redis.port'));
        return $redis;
    }

}
