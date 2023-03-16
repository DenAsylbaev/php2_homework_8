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
use GeekBrains\LevelTwo\Http\Auth;


class LogOut implements ActionInterface
    {
    private TokenAuthenticationInterface $authentication;
    private AuthTokensRepositoryInterface $authTokensRepository;

    public function __construct(
        TokenAuthenticationInterface $authentication,
        AuthTokensRepositoryInterface $authTokensRepository
    ) {
        $this->authentication = $authentication;
        $this->authTokensRepository = $authTokensRepository;
    }
    public function handle(Request $request): Response
    {
        // проверить юзера и его токен по запросу
        try {
            $author = $this->authentication->user($request);

        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        //создаем новый токен c нулевым сроком годности
        $authToken = new AuthToken(
            // Случайная строка длиной 40 символов
            $this->authentication->getToken(),
            $author->id(),
            // Срок годности - 1 день
            (new DateTimeImmutable('now'))
        );
        // Сохраняем этот токен в репозиторий
        $this->authTokensRepository->save($authToken);

        // Возвращаем успешный ответ с токеном
        return new SuccessfulResponse([
            'token inactive' => (string)$authToken->token(),
        ]);
    }
}