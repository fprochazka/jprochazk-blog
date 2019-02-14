<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Post;
use App\Entity\Comment;

class CommentController extends AbstractController
{
	//TODO: implement editComment and deleteComment

    /**
      * @Route("/post/{post_id<\d+>}/comment", name="app_blog_post_comment")
      */
    public function createComment($post_id, Request $request) {
        if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            $content = $request->request->get('content');
            if(is_string($content)) {
                $date = date_create_from_format('H:i:s Y-m-d', date('H:i:s Y-m-d'));
                $comment = new Comment();
                $post = $this->getDoctrine()->getRepository(Post::class)->find($post_id);

                $comment->setDate($date);
                $comment->setAuthor($this->getUser()->getUsername());
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
        'message' => 'Content is not of type string'],
        400);
    }

	/**
      * @Route("/post/{post_id<\d+>}/comment/edit/{comment_id<\d+>}", name="app_blog_post_comment_edit")
      */
    public function editComment($post_id, $comment_id, Request $request) {
        if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            $content = $request->request->get('content');
            $request_username = $request->request->get('current_user');
            if($this->getUser()->getUsername() == $request_username) {
	            if(is_string($content)) {
	                $entityManager = $this->getDoctrine()->getManager();

	                $comment = $this->getDoctrine()->getRepository(Comment::class)->find($comment_id);
	                $comment->setContent($content);

	                $entityManager->persist($comment);
	                $entityManager->flush();

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
        } else {
	        return new JsonResponse([
		        'status' => 'Error',
		        'message' => 'str'
	    	], 200);
	    }
    }
}
