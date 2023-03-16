<?php
namespace GeekBrains\LevelTwo\Blog;

use GeekBrains\LevelTwo\Person\Name;


class User
{
    private UUID $id;
    private string $username;
    private Name $name;
    private string $hashedPassword;

    public function __construct(
        UUID $id, 
        string $username, 
        Name $name,
        string $hashedPassword
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->hashedPassword = $hashedPassword;
    }

    // public function password(): string
    // {
    //     return $this->password;
    // }

    // Переименовали функцию
    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    // Функция для проверки предъявленного пароля
    public function checkPassword(string $password): bool
    {
        return $this->hashedPassword === self::hash($password, $this->id);
    }

    // Функция для создания нового пользователя
    public static function createFrom(
        string $username,
        string $password,
        Name $name
    ): self
    {
        $uuid = UUID::random();
        return new self(
            $uuid,
            $username,
            $name,
            self::hash($password, $uuid)
        );
    }

    // Функция для вычисления хеша
    private static function hash(
        string $password,
        UUID $uuid
        ): string
    {
        return hash('sha256', $uuid . $password);
    }

    public function __toString(): string
    {
        return "Юзер $this->id с именем $this->name и никнеймом $this->username." . PHP_EOL;

    }

    public function id(): UUID
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setName(Name $name): void
    {
        $this->name = $name;
    }

    public function getUserName(): string
    {
        return $this->username;
    }

    public function setUserName(string $username): void
    {
        $this->username = $username;
    }

}