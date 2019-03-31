<?php

namespace App\Controller;

use App\DTO\CreateCommentDto;
use App\DTO\CreatePostDto;
use App\DTO\DeletePostDto;
use App\DTO\EditPostDto;
use App\Entity\Post;
use App\Form\PostFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            'posts' => $this->postFacade->getFrontPagePosts($page),
        ]);
    }

    /**
      * @Route("/post/{id<\d+>}", name="app_blog_post_show")
      */
    public function showPost(int $id): Response {
        $post = $this->postFacade->getPostById($id);
        if($post !== null) {
            return $this->render('blog/post/show.html.twig', [
                'post' => $post
            ]);
        } else {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }
    }

    /**
      * @Route("/post/new", name="app_blog_post_new")
      */
    public function createPost(Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $form = $this->createForm(PostFormType::class, new Post());

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $formData = $form->getData();
                $post = $this->postFacade->createPost(
                    new CreatePostDto(
                        $formData->getTitle(),
                        $formData->getContent()
                    )
                );

                $this->addFlash('notice', 'Post created');

                return $this->redirectToRoute('app_blog_post_show', ['id' => $post->getId()]);
            } else {
                return $this->render('blog/post/form.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            }
        }

        return $this->render('blog/post/form.html.twig', [
            'form' => $form->createView(),
            'errors' => null
        ]);
    }

    /**
      * @Route("/post/edit/{id<\d+>}", name="app_blog_post_edit")
      */
    public function editPost(int $id, Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $post = $this->postFacade->getPostById($id);
        if($post === null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $formData = $form->getData();
                $this->postFacade->updatePost(
                    new EditPostDto(
                        $id,
                        $formData->getTitle(),
                        $formData->getContent()
                    )
                );

                $this->addFlash('notice', 'Post updated');

                return $this->redirectToRoute('app_blog_post_show', ['id' => $post->getId()]);
            } else {
                return $this->render('blog/post/form.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $form->getErrors()
                ]);
            }
        }

        return $this->render('blog/post/form.html.twig', [
            'form' => $form->createView(),
            'errors' => null
        ]);
    }

    /**
      * @Route("/post/delete/{id<\d+>}", name="app_blog_post_delete")
      */
    public function deletePost(int $id, Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $post = $this->postFacade->getPostById($id);
        if($post === null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        $this->postFacade->deletePost(
            new DeletePostDto($id)
        );

        $this->addFlash('notice', 'Post successfully deleted');

        return $this->redirectToRoute('app_blog_post_list');
    }
}

