<?php


namespace App\Facade;

use App\DTO\CreateCommentDto;
use App\DTO\DeleteCommentDto;
use App\DTO\EditCommentDto;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use App\Entity\Comment;

class CommentFacade
{
    /** @var CurrentUserProvider */
    private $userProvider;

    /** @var PostRepository */
    private $postRepository;

    /** @var CommentRepository */
    private $commentRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        CurrentUserProvider $userProvider,
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->userProvider = $userProvider;
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function createComment(CreateCommentDto $commentDto): Comment
    {
        $post = $this->postRepository->find($commentDto->getPostId());
        $author = $this->userProvider->getUser();
        $comment = new Comment($post, $author);

        $comment->setContent($commentDto->getContent());
        $post->addComment($comment);
        $author->addComment($comment);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $comment;
    }


    /**
     * @throws AccessDeniedException
     */
    public function editComment(EditCommentDto $commentDto): Comment
    {
        $current_user = $this->userProvider->getUser();
        $comment = $this->commentRepository->find($commentDto->getCommentId());

        if($current_user->getUsername() === $commentDto->getAuthorUsername()) {
            $comment->setContent($commentDto->getContent());
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
        } else {
            throw new AccessDeniedException();
        }

        return $comment;
    }


    /**
     * @throws AccessDeniedException
     */
    public function deleteComment(DeleteCommentDto $commentDto): void
    {
        $current_user = $this->userProvider->getUser();
        $comment = $this->commentRepository->find($commentDto->getCommentId());
        $post = $this->postRepository->find($commentDto->getPostId());

        if($current_user->getUsername() === $commentDto->getAuthorUsername()) {
            $post->removeComment($comment);
            $current_user->removeComment($comment);
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
        } else {
            throw new AccessDeniedException();
        }
    }
}
