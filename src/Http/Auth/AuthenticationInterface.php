<?php

namespace GeekBrains\LevelTwo\Http\Auth;

use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Blog\User;
interface AuthenticationInterface
{
    public function user(Request $request): User;
}