<?php

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$connection->exec('CREATE TABLE tokens (
        token TEXT NOT NULL
        CONSTRAINT token_primary_key
        PRIMARY KEY,
        user_uuid TEXT NOT NULL,
        expires_on TEXT NOT NULL
    );'
);