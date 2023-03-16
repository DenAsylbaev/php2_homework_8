<?php

use GeekBrains\LevelTwo\Blog\Commands\Users\CreateUser;
use GeekBrains\LevelTwo\Blog\Commands\Posts\DeletePost;
use GeekBrains\LevelTwo\Blog\Commands\Users\UpdateUser;
use GeekBrains\LevelTwo\Blog\Commands\FakeData\PopulateDB;

use Faker\Container;


use Symfony\Component\Console\Application;


$container = require __DIR__ . '/bootstrap_new.php';

// Создаём объект приложения
$application = new Application();
// // Перечисляем классы команд
// $commandsClasses = [
//     CreateUser::class,
// ];
// foreach ($commandsClasses as $commandClass) {
//     // Посредством контейнера
//     // создаём объект команды
//     $command = $container->get($commandClass);
//     // Добавляем команду к приложению
//     $application->add($command);
// }
// // Запускаем приложение
// $application->run();

$commandsClasses = [
        CreateUser::class,
        // Добавили команду удаления статей
        DeletePost::class,
        // Добавили команду обновления пользователя
        UpdateUser::class,
        // Добавили команду генерирования тестовых данных
        PopulateDB::class,
];
foreach ($commandsClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}
$application->run();