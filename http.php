<?php

use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Actions\Users\FindByUsername;
use GeekBrains\LevelTwo\Http\Actions\Posts\FindPostByUuid;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\Actions\Posts\DeletePost;
use GeekBrains\LevelTwo\Http\Actions\Comments\CreateComment;
use GeekBrains\LevelTwo\Http\Actions\Likes\CreateLike;
use Psr\Log\LoggerInterface;
use GeekBrains\LevelTwo\Http\Actions\Auth\LogIn;
use GeekBrains\LevelTwo\Http\Actions\Auth\LogOut;


$container = require __DIR__ . '/bootstrap_new.php';


// Создаём объект запроса из суперглобальных переменных
$request = new Request(
    $_GET, 
    $_SERVER,
    file_get_contents('php://input'),
);

$logger = $container->get(LoggerInterface::class);

try {
    // Пытаемся получить путь из запроса
    $path = $request->path();

} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());

    // Отправляем неудачный ответ,
    // если по какой-то причине
    // не можем получить путь
    (new ErrorResponse($e->getMessage()))->send();

    // Выходим из программы
    return;
}

$routes = [
    // Добавили ещё один уровень вложенности
    // для отделения маршрутов,
    // применяемых к запросам с разными методами
    'GET' => [
        '/users/show' => FindByUsername::class,
        '/posts/show' =>  FindPostByUuid::class,
    ],
    'POST' => [
        // Добавили новый маршрут
        '/posts/create' => CreatePost::class,
        '/posts/comment' => CreateComment::class,
        '/posts/like' => CreateLike::class,

        // Добавили маршрут обмена пароля на токен
        '/login' => LogIn::class,
        '/logout' => LogOut::class,

    ],
    'DELETE' => [
        '/posts' => DeletePost::class,
    ],
];

$method = $request->method();

// Если у нас нет маршрута для пути из запроса -
// отправляем неуспешный ответ
if (!array_key_exists($method, $routes)
    || !array_key_exists($path, $routes[$method])) {
    
    // Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);

    (new ErrorResponse('Not found'))->send();
    return;
}
// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Выбираем действие по методу и пути
$actionClassName = $routes[$method][$path];

$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);
} catch (AppException $e) {
    // Логируем сообщение с уровнем ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);

    (new ErrorResponse($e->getMessage()))->send();
}
$response->send();
