<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Person\Name;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use \PDO;
use \PDOStatement;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    private PDO $connection;
    public function __construct(PDO $connection) 
        {
            $this->connection = $connection;
        }
        
    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (
                first_name, 
                last_name, 
                uuid, 
                username, 
                password
            )
            VALUES (
                :first_name, 
                :last_name, 
                :uuid, 
                :username, 
                :password
            )
            ON CONFLICT (uuid) DO UPDATE SET
                first_name = :first_name,
                last_name = :last_name'
            );
            // Выполняем запрос с конкретными значениями
            $statement->execute([
            ':first_name' => $user->getName()->getFirstName(),
            ':last_name' => $user->getName()->getLastName(),
            ':uuid' => (string)$user->id(),
            ':username' => $user->getUsername(),
            ':password' => $user->hashedPassword()

            ]);
    }
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = ?'
        );
        $statement->execute([(string)$uuid]);
        return $this->getUser($statement, $uuid);

    }

    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
            ':username' => $username,
        ]);
        return $this->getUser($statement, $username);
    }

    private function getUser(PDOStatement $statement, string $errorname): User
    {
        // var_dump($result);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        // var_dump($result);

        
        if (false === $result) {
            throw new UserNotFoundException(
                "Cannot find user: $errorname"
            );
        }   

        // var_dump($result);
        // die();

        return new User(
            new UUID($result['uuid']),
            $result['username'],
            new Name($result['first_name'], $result['last_name']),
            $result['password']
        );
    }
}