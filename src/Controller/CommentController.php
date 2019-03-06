<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Facade\CommentFacade;

class CommentController extends AbstractController
{
	private $commentFacade;

	public function __construct(
	    CommentFacade $commentFacade
	)
	{
	    $this->commentFacade = $commentFacade;
	}

	/**
     * @Route("/post/{post_id<\d+>}/comment", name="app_blog_post_comment")
     */
    public function createComment(int $post_id, Request $request): JsonResponse
    {
        return new JsonResponse($this->commentFacade->createComment($post_id, $request), 200);
    }

	/**
     * @Route("/post/{post_id<\d+>}/comment/edit/{comment_id<\d+>}", name="app_blog_post_comment_edit")
     */
    public function editComment(int $post_id, int $comment_id, Request $request): JsonResponse
    {
        return new JsonResponse($this->commentFacade->editComment($post_id, $comment_id, $request), 200);
    }

	/**
      * @Route("/post/{post_id<\d+>}/comment/delete/{comment_id<\d+>}", name="app_blog_post_comment_delete")
      */
    public function deleteComment(int $post_id, int $comment_id, Request $request): JsonResponse
    {
        return new JsonResponse($this->commentFacade->deleteComment($post_id, $comment_id, $request), 200);
    }
}
