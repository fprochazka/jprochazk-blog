<?php

namespace App\Controller;

use App\DTO\DeleteCommentDto;
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

	public function __construct(
	    CommentFacade $commentFacade,
        CommentResponseFactory $commentFactory
	)
	{
	    $this->commentFacade = $commentFacade;
	    $this->commentFactory = $commentFactory;
	}

	/**
     * @Route("/post/{post_id<\d+>}/comment", name="app_blog_post_comment")
     */
    public function createComment(int $post_id, Request $request): JsonResponse
    {
        $content = $request->request->get('content');
        if($content !== null) {
            try {
                $comment = $this->commentFacade->createComment(
                    new CreateCommentDto(
                        $content,
                        $post_id
                    )
                );

                return new JsonResponse($this->commentFactory->getCommentJson($comment), 200);
            } catch (\Exception $e) {
                return new JsonResponse(['message' => $e->getMessage()], 500);
            }
        } else {
            return new JsonResponse(['message' => 'null_content'], 500);
        }
    }

	/**
     * @Route("/post/{post_id<\d+>}/comment/edit/{comment_id<\d+>}", name="app_blog_post_comment_edit")
     */
    public function editComment(int $post_id, int $comment_id, Request $request): JsonResponse
    {
        $content = $request->request->get('content');
        $comment_editor_username = $request->request->get('current_user');
        if($content !== null && $comment_editor_username !== null) {
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
                return new JsonResponse(['message' => $e->getMessage()], 500);
            }
        } else {
            return new JsonResponse(['message' => 'null_content_or_username'], 500);
        }
    }

	/**
      * @Route("/post/{post_id<\d+>}/comment/delete/{comment_id<\d+>}", name="app_blog_post_comment_delete")
      */
    public function deleteComment(int $post_id, int $comment_id, Request $request): JsonResponse
    {
        $comment_deleter_username = $request->request->get('current_user');

        if($comment_deleter_username !== null) {
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
                return new JsonResponse(['message' => $e->getMessage()], 500);
            }
        } else {
            return new JsonResponse(['message' => 'null_username'], 500);
        }
    }
}

