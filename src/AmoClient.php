<?php
declare(strict_types=1);

namespace AuthService;

use ddlzz\AmoAPI\Client;
use ddlzz\AmoAPI\CredentialsManager;
use ddlzz\AmoAPI\Request\Curl;
use ddlzz\AmoAPI\Request\DataSender;
use ddlzz\AmoAPI\Request\UrlBuilder;
use ddlzz\AmoAPI\SettingsStorage;
use Exception;

/**
 * Класс для работы с amoCRM
 */
class AmoClient
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var DataSender
     */
    private $dataSender;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param string $domain
     * @param string $login
     * @param string $hash
     */
    public function auth(string $domain, string $login, string $hash)
    {
        $this->login = $login;

        $credentials = new CredentialsManager($domain, $login, $hash);
        $settings = new SettingsStorage();
        $this->dataSender = new DataSender(new Curl(), $settings);
        $this->urlBuilder = new UrlBuilder($settings, $domain);

        if (!file_exists(dirname($settings->getCookiePath()))) {
            mkdir(dirname($settings->getCookiePath())); // bugfix
        }
        // Попытка авторизации с переданными параметрами
        new Client($credentials, $this->dataSender, $settings);
    }

    /**
     * Выбор данных текущего пользователя
     *
     * @return array
     * @throws Exception
     */
    public function getCurrentUser(): array
    {
        $url = $this->urlBuilder->buildMethodUrl('current');
        $result = json_decode($this->dataSender->send($url), true);
        if (empty($result)) {
            throw new Exception('Failed to retrieve account info');
        }

        foreach ($result['_embedded']['users'] as $user) {
            if ($user['login'] == $this->login) {
                return $user;
            }
        }

        throw new Exception('Current user not found');
    }
}
