<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Comment;

class CommentController extends AbstractController
{

	/** @var PostRepository */
	private $postRepository;

	/** @var CommentRepository */
	private $commentRepository;

	public function __construct(
		PostRepository $postRepository,
		CommentRepository $commentRepository
	)
	{
		$this->postRepository = $postRepository;
		$this->commentRepository = $commentRepository;
	}

	/**
     * @Route("/post/{post_id<\d+>}/comment", name="app_blog_post_comment")
     */
    public function createComment(int $post_id, Request $request): JsonResponse {
        if($request->isXmlHttpRequest() || $request->query->get('showJson') === 1) {
            $content = $request->request->get('content');
            if(is_string($content)) {

                $comment = new Comment();
                $post = $this->postRepository->find($post_id);

                $date = new \DateTimeImmutable();
                $comment->setDate($date);

                $comment->setAuthor($this->getUser()->getUsername());

                /** @var string $content */
                $comment->setContent($content);

                $post->addComment($comment);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($comment);
                $entityManager->flush();

                $responseData = [
                	'id' => $comment->getId(),
                    'author' => $this->getUser()->getUsername(),
                    'date' => $date->format('Y-m-d, H:i:s'),
                    'content' => $content
                ];

                return new JsonResponse([
                'status' => 'OK',
                'message' => $responseData],
                200);
            }
        }
        return new JsonResponse([
            'status' => 'Error',
            'message' => 'xml'
        ], 200);
    }

	/**
     * @Route("/post/{post_id<\d+>}/comment/edit/{comment_id<\d+>}", name="app_blog_post_comment_edit")
     */
    public function editComment(int $post_id, int $comment_id, Request $request): JsonResponse {
        if($request->isXmlHttpRequest() || $request->query->get('showJson') === 1) {
            $content = $request->request->get('content');
            $request_username = $request->request->get('current_user');
            if($this->getUser()->getUsername() === $request_username) {
	            if(is_string($content)) {
	                $entityManager = $this->getDoctrine()->getManager();

	                $post = $this->postRepository->find($post_id);
	                $comment = $this->commentRepository->find($comment_id);

	                if($post->getComments()->contains($comment)) {
                        $comment->setContent($content);

                        $entityManager->persist($comment);
                        $entityManager->flush();
                    }

	                $responseData = [
	                    'content' => $content
	                ];

	                return new JsonResponse([
		                'status' => 'OK',
		                'message' => $responseData
	            	], 200);
	            }
	        } else {
		       	return new JsonResponse([
		        	'status' => 'Error',
		        	'message' => 'perm',
		        ], 200);
		    }
        }
        return new JsonResponse([
            'status' => 'Error',
            'message' => 'xml'
        ], 200);
    }

	/**
      * @Route("/post/{post_id<\d+>}/comment/delete/{comment_id<\d+>}", name="app_blog_post_comment_delete")
      */
    public function deleteComment(int $post_id, int $comment_id, Request $request): JsonResponse {
        if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            $request_username = $request->request->get('current_user');
            if($this->getUser()->getUsername() == $request_username) {
                $entityManager = $this->getDoctrine()->getManager();

                $comment = $this->commentRepository->find($comment_id);
                $post = $this->postRepository->find($post_id);

                $post->removeComment($comment);

                $entityManager->remove($comment);
                $entityManager->flush();

                return new JsonResponse([
	                'status' => 'OK',
	                'message' => 'deleted'
            	], 200);
	        } else {
		       	return new JsonResponse([
		        	'status' => 'Error',
		        	'message' => 'perm',
		        ], 200);
		    }
        }
        return new JsonResponse([
            'status' => 'Error',
            'message' => 'xml'
        ], 200);
    }
}
