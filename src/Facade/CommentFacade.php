<?php


namespace App\Facade;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use App\Entity\Comment;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentFacade
{
    /** @var UserInterface|null  */
    private $user;

    /** @var PostRepository  */
    private $postRepository;

    /** @var CommentRepository  */
    private $commentRepository;

    /** @var EntityManagerInterface  */
    private $entityManager;

    public function __construct(
        Security $security,
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->user = $security->getUser();
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
    }

    private function saveComment(Comment $comment): bool
    {
        try {
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    private function removeComment(Comment $comment): bool
    {
        try {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    public function createComment(int $post_id, Request $request): array
    {
        $responseData = [
            'status' => 'Error',
            'message' => 'xml'
        ];

        if($request->isXmlHttpRequest() || $request->query->get('showJson') === 1) {
            /** @var string $content */
            $content = $request->request->get('content');
            /** @var User $user */
            $user = $this->user;

            $post = $this->postRepository->find($post_id);
            $comment = new Comment();

            $date = new \DateTimeImmutable();
            $comment->setDate($date);
            $comment->setAuthor($user->getUsername());
            $comment->setContent($content);

            $post->addComment($comment);
            $this->saveComment($comment);

            $responseData['status'] = 'OK';
            $responseData['message'] = [
                'id' => $comment->getId(),
                'author' => $user->getUsername(),
                'date' => $date->format('Y-m-d, H:i:s'),
                'content' => $content,
            ];
        }

        return $responseData;
    }

    public function editComment(int $post_id, int $comment_id, Request $request): array
    {
        $responseData = [
            'status' => 'Error',
            'message' => 'xml'
        ];

        if($request->isXmlHttpRequest() || $request->query->get('showJson') === 1) {
            /** @var string $content */
            $content = $request->request->get('content');
            $request_username = $request->request->get('current_user');

            /** @var User $user */
            $user = $this->user;
            if($user->getUsername() === $request_username) {
                $post = $this->postRepository->find($post_id);
                $comment = $this->commentRepository->find($comment_id);

                if($post->getComments()->contains($comment)) {
                    $comment->setContent($content);
                    $this->saveComment($comment);
                }

                $responseData['status'] = 'OK';
                $responseData['message'] = [
                    'content' => $content
                ];
            } else {
                $responseData['message'] = 'perm';
            }
        }
        return $responseData;
    }

    public function deleteComment(int $post_id, int $comment_id, Request $request): array
    {
        $responseData = [
            'status' => 'Error',
            'message' => 'xml'
        ];

        if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            /** @var User $user */
            $user = $this->user;
            $request_username = $request->request->get('current_user');
            if($user->getUsername() == $request_username) {

                $comment = $this->commentRepository->find($comment_id);
                $post = $this->postRepository->find($post_id);

                $post->removeComment($comment);
                $this->removeComment($comment);

                $responseData['status'] = 'OK';
                $responseData['message'] = 'deleted';
            } else {
                $responseData['message'] = 'perm';
            }
        }

        return $responseData;
    }
}