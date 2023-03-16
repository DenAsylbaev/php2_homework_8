<?php

use GeekBrains\LevelTwo\Blog\Container\DIContainer;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepositories\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepositories\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepositories\LikesRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepositories\SqliteLikesRepository;
use GeekBrains\LevelTwo\Http\Auth\IdentificationInterface;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUuidIdentification;
use GeekBrains\LevelTwo\Http\Auth\PasswordAuthentication;
use GeekBrains\LevelTwo\Http\Auth\PasswordAuthenticationInterface;
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\Auth\BearerTokenAuthentication;
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\Internet;
use Faker\Provider\ru_RU\Person;
use Faker\Provider\ru_RU\Text;

// use Dotenv\Dotenv;

use Dotenv\Dotenv;

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения из файла .env
Dotenv::createImmutable(__DIR__)->safeLoad();

// Создаём объект контейнера ..
$container = new DIContainer();

$logger = (new Logger('blog'));

// Создаём объект генератора тестовых данных
$faker = new \Faker\Generator();
// Инициализируем необходимые нам виды данных
$faker->addProvider(new Person($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Lorem($faker));

// Включаем логирование в файлы,
// если переменная окружения LOG_TO_FILES
// содержит значение 'yes'
if ('yes' === $_SERVER['LOG_TO_FILES']) {
    $logger
    ->pushHandler(new StreamHandler(
        __DIR__ . '/logs/blog.log'
    ))
    ->pushHandler(new StreamHandler(
        __DIR__ . '/logs/blog.error.log',
        Logger::ERROR,
        false,
    ));
}

// Включаем логирование в консоль,
// если переменная окружения LOG_TO_CONSOLE
// содержит значение 'yes'
if ('yes' === $_SERVER['LOG_TO_CONSOLE']) {
    $logger
    ->pushHandler(
    new StreamHandler("php://stdout")
    );
}

// $container->bind(
//     AuthenticationInterface::class,
//     PasswordAuthentication::class
// );
$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);

$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);


$container->bind(
    LoggerInterface::class,
    $logger
);

// .. и настраиваем его:
// 1. подключение к БД
$container->bind(
    PDO::class,
    // new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
    new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
);

// 2. репозиторий статей
$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);
// 3. репозиторий пользователей
$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);
// 4. репозиторий комментериев
$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
);

$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepository::class
);

$container->bind(
    IdentificationInterface::class,
    JsonBodyUuidIdentification::class
);

$container->bind(
    \Faker\Generator::class,
    $faker
);

    // Возвращаем объект контейнера
    return $container;