<?php
namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use Psr\Log\LoggerInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;


class CreatePost implements ActionInterface
{
    private PostsRepositoryInterface $postsRepository;
    private TokenAuthenticationInterface $authentication;
    private LoggerInterface $logger;

    // Внедряем репозитории статей и пользователей
    public function __construct(
        PostsRepositoryInterface $postsRepository,
        TokenAuthenticationInterface $authentication,
        LoggerInterface $logger
    ) {
        $this->postsRepository = $postsRepository;
        $this->authentication = $authentication;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        try {
            $author = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // // Генерируем UUID для новой статьи
        $newPostUuid = UUID::random();

        try {
            // Пытаемся создать объект статьи
            // из данных запроса
            $post = new Post(
            $newPostUuid,
            $author,
            $request->jsonBodyField('title'),
            $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        
        // Сохраняем новую статью в репозитории
        if($this->postsRepository->save($post)) {
            // Логируем UUID новой статьи
            $this->logger->info("Post created: $newPostUuid"); 
            // Возвращаем успешный ответ,
            // содержащий UUID новой статьи
            return new SuccessfulResponse([
                'uuid' => (string)$newPostUuid,
            ]);
        } else {
            // Логируем сообщение с уровнем WARNING
            // $this->logger->warning("Can't create post");
            // и возвращаем неуспешынй ответ
            return new ErrorResponse("Can't create post");
        }
    }
}