# Запуск
Для тестирования: php -S localhost:8080 -t public/
# Использование
## Получение токена
POST-запрос на /token c JSON `{"login":"example@mail.com","api_key":"72d8bdb6536cd47eb5c89123e7c86de4821b3892"}`

В случае успеха возвращается JSON `{"token":"703b3c9b7425acd40d5f89432e18dfdc947e043fb68f03c9be1756767d689898"}`

## Обмен токена
GET-запрос на /token/\<token\>

В случае успеха возвращается JSON `{...}`
