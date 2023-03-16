<?php

$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$connection->exec('ALTER TABLE users ADD password TEXT;');