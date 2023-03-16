<?php

namespace GeekBrains\LevelTwo\Blog;

use DateTimeImmutable;
use GeekBrains\LevelTwo\Blog\UUID;

class AuthToken
{
    // Строка токена
    private string $token;
    // UUID пользователя
    private UUID $userUuid;
    // Срок годности
    private DateTimeImmutable $expiresOn;
    public function __construct(
        // Строка токена
        string $token,
        // UUID пользователя
        UUID $userUuid,
        // Срок годности
        DateTimeImmutable $expiresOn
    ) {
        $this->token = $token;
        $this->userUuid = $userUuid;
        $this->expiresOn = $expiresOn;
    }

    public function token(): string
    {
        return $this->token;
    }
    public function userUuid(): UUID
    {
        return $this->userUuid;
    }
    public function expiresOn(): DateTimeImmutable
    {
        return $this->expiresOn;
    }
}