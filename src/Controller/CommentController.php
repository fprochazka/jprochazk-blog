<?php

namespace App\Controller;

use App\DTO\DeleteCommentDto;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Facade\CommentFacade;
use App\ResponseFactory\CommentResponseFactory;
use App\DTO\CreateCommentDto;
use App\DTO\EditCommentDto;

class CommentController extends AbstractController
{
    /** @var CommentFacade */
	private $commentFacade;

	/** @var CommentResponseFactory */
	private $commentFactory;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(
	    CommentFacade $commentFacade,
        CommentResponseFactory $commentFactory,
        LoggerInterface $logger
	)
	{
	    $this->commentFacade = $commentFacade;
	    $this->commentFactory = $commentFactory;
	    $this->logger = $logger;
	}

	/**
     * @Route("/post/{post_id<\d+>}/comment", name="app_blog_post_comment")
     */
    public function createComment(int $post_id, Request $request): JsonResponse
    {
        $content = $request->request->get('content');
        $csrf_token = $request->request->get('csrf_token');
        if($content !== null) {
            if($this->isCsrfTokenValid($this->getUser()->getUsername(), $csrf_token)) {
                try {
                    $comment = $this->commentFacade->createComment(
                        new CreateCommentDto(
                            $content,
                            $post_id
                        )
                    );

                    return new JsonResponse($this->commentFactory->getCommentJson($comment), 200);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    return new JsonResponse(['message' => 'Server encountered an error while saving your comment.'], 500);
                }
            } else {
                $this->logger->error('CSRF token invalid: ["token" => '.$csrf_token.'], ["username" => '.$this->getUser()->getUsername().']');
                return new JsonResponse(['message' => 'Error while deleting comment'], 500);
            }
        } else {
            return new JsonResponse(['message' => 'You cannot post an empty comment!'], 500);
        }
    }

	/**
     * @Route("/post/{post_id<\d+>}/comment/edit/{comment_id<\d+>}", name="app_blog_post_comment_edit")
     */
    public function editComment(int $post_id, int $comment_id, Request $request): JsonResponse
    {
        $content = $request->request->get('content');
        $csrf_token = $request->request->get('csrf_token');
        $comment_editor_username = $request->request->get('current_user');
        if($content !== null && $comment_editor_username !== null) {
            if($this->isCsrfTokenValid($comment_editor_username, $csrf_token)) {
                try {
                    $comment = $this->commentFacade->editComment(
                        new EditCommentDto(
                            $content,
                            $comment_id,
                            $comment_editor_username
                        )
                    );

                    return new JsonResponse($this->commentFactory->getCommentJson($comment), 200);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    return new JsonResponse(['message' => 'Server encountered an error while saving your comment.'], 500);
                }
            } else {
                $this->logger->error('CSRF token invalid: ["token" => '.$csrf_token.'], ["username" => '.$comment_editor_username.']');
                return new JsonResponse(['message' => 'Error while deleting comment'], 500);
            }
        } else {
            return new JsonResponse(['message' => 'You cannot post an empty comment!'], 500);
        }
    }

	/**
      * @Route("/post/{post_id<\d+>}/comment/delete/{comment_id<\d+>}", name="app_blog_post_comment_delete")
      */
    public function deleteComment(int $post_id, int $comment_id, Request $request): JsonResponse
    {
        $comment_deleter_username = $request->request->get('current_user');
        $csrf_token = $request->request->get('csrf_token');

        if($comment_deleter_username !== null) {
            if($this->isCsrfTokenValid($comment_deleter_username, $csrf_token)) {
                try {
                    $this->commentFacade->deleteComment(
                        new DeleteCommentDto(
                            $comment_id,
                            $post_id,
                            $comment_deleter_username
                        )
                    );
                    return new JsonResponse(['message' => 'Success'], 200);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    return new JsonResponse(['message' => 'Server encountered an error while deleting the comment.'], 500);
                }
            } else {
                $this->logger->error('CSRF token invalid: ["token" => '.$csrf_token.'], ["username" => '.$comment_deleter_username.']');
                return new JsonResponse(['message' => 'Error while deleting comment'], 500);
            }
        } else {
            return new JsonResponse(['message' => 'Log in first!'], 500);
        }
    }
}

