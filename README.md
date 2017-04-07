Legacy APP PROXY
================

In order to foward symfony request for non-existing routes to legacy APP.

1. Install the following packages
```
composer require "symfony/psr-http-message-bridge: ^1.0"
composer require "zendframework/zend-diactoros: ^1.3"
composer require "guzzlehttp/guzzle: ^6.2"
```

2.

```
php -S localhost:8080 -t web/
```

```
http://localhost:8080/api/users/1
https://reqres.in/api/users/1

http://localhost:8080/api/users
https://reqres.in/api/users

```

3. Let's open `app/app.php` file and read comments

4. Running tests `vendor/bin/phpunit`

To test rest proxy requests has been used [https://reqres.in/](https://reqres.in/)

