<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Facade\AuthenticationFacade;
use App\Facade\PostFacade;

class PostController extends AbstractController
{
	/** @var PostFacade */
	private $postFacade;

	/** @var AuthenticationFacade */
    private $authFacade;

    /** @var PostRepository */
	private $postRepository;

	public function __construct(
	    AuthenticationFacade $authFacade,
		PostFacade $postFacade
	)
	{
	    $this->authFacade = $authFacade;
		$this->postFacade = $postFacade;
	}

	/**
      * @Route("/{page<\d+>?1}", name="app_blog_post_list")
      */
    public function postList(int $page): Response
    {
        return $this->render('blog/post/list.html.twig', [
            "posts" => $this->postFacade->getFrontPagePosts($page),
        ]);
    }

    /**
      * @Route("/post/{id<\d+>}", name="app_blog_post_show")
      */
    public function showPost(int $id): Response {
        $post = $this->postFacade->getSinglePost($id);
        if($post !== null) {
            return $this->render('blog/post/show.html.twig', [
                "post" => $post
            ]);
        } else {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }
    }

    /**
      * @Route("/post/new", name="app_blog_post_new")
      */
    public function createPost(Request $request): Response {
        if(($authenticationError = $this->authFacade->getAuthenticationError()) !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $response = $this->postFacade->createPost($request);
        if($response['status'] === 200) {
            return $this->redirectToRoute('app_blog_post_show', ['id' => $response['post_id']]);
        }

        return $this->render('blog/post/form.html.twig', [
            'form' => $this->postFacade->getPostFormView(),
            'error' => $response['error']
        ]);
    }

    /**
      * @Route("/post/edit/{id<\d+>}", name="app_blog_post_edit")
      */
    public function editPost(int $id, Request $request): Response {
        if(($authenticationError = $this->authFacade->getAuthenticationError()) !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $response = $this->postFacade->editPost($id, $request);
        if($response['status'] === 200) {
            return $this->redirectToRoute('app_blog_post_show', ['id' => $response['post_id']]);
        } else if($response['status'] === 404) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        return $this->render('blog/post/form.html.twig', [
            'form' => $this->postFacade->getPostFormView($response['post_data']),
            'error' => $response['error']
        ]);
    }

    /**
      * @Route("/post/delete/{id<\d+>}", name="app_blog_post_delete")
      */
    public function deletePost(int $id, Request $request): Response {
        if(($authenticationError = $this->authFacade->getAuthenticationError()) !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $response = $this->postFacade->deletePost($id);
        if($response['status'] === 404) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        return $this->redirectToRoute('app_blog_post_list');
    }
}
