<?php

namespace GeekBrains\LevelTwo\Http\Actions\Auth;

use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\PasswordAuthenticationInterface;
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Blog\AuthToken;
use DateTimeImmutable;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;


class LogIn implements ActionInterface
    {
    // Авторизация по паролю
    private PasswordAuthenticationInterface $passwordAuthentication;
    // Репозиторий токенов
    private AuthTokensRepositoryInterface $authTokensRepository;
    public function __construct(
        // Авторизация по паролю
        PasswordAuthenticationInterface $passwordAuthentication,
        // Репозиторий токенов
        AuthTokensRepositoryInterface $authTokensRepository
    ) {
        $this->passwordAuthentication = $passwordAuthentication;
        $this->authTokensRepository = $authTokensRepository;

    }
    public function handle(Request $request): Response
    {
        // Аутентифицируем пользователя
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Генерируем токен
        $authToken = new AuthToken(
            // Случайная строка длиной 40 символов
            bin2hex(random_bytes(40)),
            $user->id(),
            // Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );

        // Сохраняем токен в репозиторий
        $this->authTokensRepository->save($authToken);

        // Возвращаем успешный ответ с токеном
        return new SuccessfulResponse([
            'token' => (string)$authToken->token(),
        ]);
    }
}