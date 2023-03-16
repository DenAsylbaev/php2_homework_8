<?php

namespace GeekBrains\LevelTwo\Http\Auth;

use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthTokenNotFoundException;
use DateTimeImmutable;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Blog\AuthToken;



// Bearer — на предъявителя
class BearerTokenAuthentication implements TokenAuthenticationInterface
{
    private const HEADER_PREFIX = 'Bearer ';
    // Репозиторий токенов
    private AuthTokensRepositoryInterface $authTokensRepository;
    // Репозиторий пользователей
    private UsersRepositoryInterface $usersRepository;
    private string $token;


    public function __construct(
        // Репозиторий токенов
        AuthTokensRepositoryInterface $authTokensRepository,
        // Репозиторий пользователей
        UsersRepositoryInterface $usersRepository
    ) {
        $this->authTokensRepository = $authTokensRepository;
        $this->usersRepository = $usersRepository;

    }
    public function user(Request $request): User
    {
        // Получаем HTTP-заголовок
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }

        // Проверяем, что заголовок имеет правильный формат
        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new AuthException("Malformed token: [$header]");
        }
        // Отрезаем префикс Bearer
        $token = mb_substr($header, strlen(self::HEADER_PREFIX));
        // Ищем токен в репозитории
        try {
            $authToken = $this->authTokensRepository->get($token);
            // присваеваем полученный токен свойству класса token, чтобы могли его получить для logout
            $this->token = $token;
        } catch (AuthTokenNotFoundException $e) {
            throw new AuthException("Bad token: [$token]");
        }
        // Проверяем срок годности токена
        if ($authToken->expiresOn() <= new DateTimeImmutable()) {
            throw new AuthException("Token expired: [$token]");
        }
        // Получаем UUID пользователя из токена
        $userUuid = $authToken->userUuid();
        // Ищем и возвращаем пользователя
        return $this->usersRepository->get($userUuid);
    }

    public function getToken(): string
    {
        return $this->token;
    }

}