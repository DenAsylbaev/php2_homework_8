<?php
namespace GeekBrains\LevelTwo\Http\Auth;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;

class PasswordAuthentication implements PasswordAuthenticationInterface
{
    private UsersRepositoryInterface $usersRepository;

    public function __construct(
        UsersRepositoryInterface $usersRepository
    ) {
        $this->usersRepository = $usersRepository;
    }
    public function user(Request $request): User
    {
        // 1. Идентифицируем пользователя
        try {
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }
        try {
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }
        // 2. Аутентифицируем пользователя
        // Проверяем, что предъявленный пароль
        // соответствует сохранённому в БД
        try {
            $password = $request->jsonBodyField('password');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }
        
        // Проверяем пароль методом пользователя
        if (!$user->checkPassword($password)) {
                throw new AuthException('Wrong password');
            }
        
        return $user;
    }
}