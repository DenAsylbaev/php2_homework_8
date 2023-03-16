<?php
namespace GeekBrains\LevelTwo\Http\Auth;
interface TokenAuthenticationInterface extends AuthenticationInterface
{
    public function getToken();
}