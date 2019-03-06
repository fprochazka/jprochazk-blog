<?php


namespace App\Facade;


use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use App\Entity\Comment;
use App\Entity\Post;

class CommentFacade
{

    private $user;
    private $postRepository;
    private $commentRepository;
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

    private function saveComment(Comment $comment)
    {
        try {
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    private function removeComment(Comment $comment)
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
            if(is_string($content)) {
                $post = $this->postRepository->find($post_id);
                $comment = new Comment();

                $date = new \DateTimeImmutable();
                $comment->setDate($date);
                $comment->setAuthor($this->user->getUsername());
                $comment->setContent($content);

                $post->addComment($comment);
                $this->saveComment($comment);

                $responseData['status'] = 'OK';
                $responseData['message'] = [
                    'id' => $comment->getId(),
                    'author' => $this->user->getUsername(),
                    'date' => $date->format('Y-m-d, H:i:s'),
                    'content' => $content,
                ];
            } else {
                $responseData['message'] = 'invalid_form';
            }
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
            if($this->user->getUsername() === $request_username) {
                if(is_string($content)) {

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
                    $responseData['message'] = 'invalid_form';
                }
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
            $request_username = $request->request->get('current_user');
            if($this->user->getUsername() == $request_username) {

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