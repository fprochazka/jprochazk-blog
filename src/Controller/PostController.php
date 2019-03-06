<?php

namespace App\Controller;

use App\Facade\PostFacade;
use App\Repository\PostRepository;
use App\Facade\AuthenticationFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\Post;
use App\Form\PostFormType;

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
            $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }
    }

    /**
      * @Route("/post/new", name="app_blog_post_new")
      */
    public function createPost(Request $request): Response {
        $this->authFacade->checkAuthentication();

        $returnObject = $this->postFacade->createPost($request);
        if($returnObject['status'] === 200) {
            return $this->redirectToRoute('app_blog_post_show', ['id' => $returnObject['post_id']]);
        }

        return $this->render('blog/post/form.html.twig', [
            'form' => $this->postFacade->getPostFormView(),
            'error' => $returnObject['error']
        ]);
    }

    /**
      * @Route("/post/edit/{id<\d+>}", name="app_blog_post_edit")
      */
    public function editPost(int $id, Request $request): Response {
        $this->authFacade->checkAuthentication();

        $returnObject = $this->postFacade->editPost($id, $request);
        if($returnObject['status'] === 200) {
            return $this->redirectToRoute('app_blog_post_show', ['id' => $returnObject['post_id']]);
        } else if($returnObject['status'] === 404) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        return $this->render('blog/post/form.html.twig', [
            'form' => $this->postFacade->getPostFormView($returnObject['post_data']),
            'error' => $returnObject['error']
        ]);
    }

    /**
      * @Route("/post/delete/{id<\d+>}", name="app_blog_post_delete")
      */
    public function deletePost(int $id, Request $request): Response {
        $this->authFacade->checkAuthentication();

        $returnArray = $this->postFacade->deletePost($id);
        if($returnArray['status'] === 404) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        return $this->redirectToRoute('app_blog_post_list');
    }
}
