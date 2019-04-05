<?php

namespace App\Controller;

use App\DTO\CreatePostDto;
use App\DTO\DeletePostDto;
use App\DTO\EditPostDto;
use App\Entity\Post;
use App\Form\PostDeleteType;
use App\Form\PostFormType;
use Psr\Log\LoggerInterface;
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

    /** @var LoggerInterface */
    private $logger;

	public function __construct(
	    AuthenticationFacade $authFacade,
		PostFacade $postFacade,
        LoggerInterface $logger
	)
	{
	    $this->authFacade = $authFacade;
		$this->postFacade = $postFacade;
		$this->logger = $logger;
	}

	/**
      * @Route("/{page<\d+>?1}", name="app_blog_post_list")
      */
    public function postList(int $page): Response
    {
        return $this->render('blog/post/list.html.twig', [
            'posts' => $this->postFacade->getFrontPagePosts($page),
            'pageNum' => $page,
            'hasNextPage' => $this->postFacade->hasNextPage($page)
        ]);
    }

    /**
      * @Route("/post/{post_id<\d+>}", name="app_blog_post_show")
      */
    public function showPost(int $post_id): Response {
        $post = $this->postFacade->getPostById($post_id);
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
                try {
                    $post = $this->postFacade->createPost(
                        new CreatePostDto(
                            $formData->getTitle(),
                            $formData->getContent()
                        )
                    );

                    $this->addFlash('notice', 'Post created');
                    return $this->redirectToRoute('app_blog_post_show', ['post_id' => $post->getId()]);

                } catch(\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('notice', 'Failed to save post');
                }
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
      * @Route("/post/edit/{post_id<\d+>}", name="app_blog_post_edit")
      */
    public function editPost(int $post_id, Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $form = $this->createForm(PostFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $post = $this->postFacade->getPostById($post_id);
                if($post === null) {
                    return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
                }
                $formData = $form->getData();
                try {
                    $this->postFacade->updatePost(
                        new EditPostDto(
                            $post_id,
                            $formData->getTitle(),
                            $formData->getContent()
                        )
                    );

                    $this->addFlash('notice', 'Post updated');
                    return $this->redirectToRoute('app_blog_post_show', ['id' => $post->getId()]);
                } catch(\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('notice', 'Failed to save post');
                }
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
      * @Route("/post/delete/{post_id<\d+>}/{admin<\d+>?0}", name="app_blog_post_delete")
      */
    public function deletePost(int $post_id, int $admin, Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $post = $this->postFacade->getPostById($post_id);
        if($post === null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => '404']);
        }

        $form = $this->createForm(PostDeleteType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                try {
                    $this->postFacade->deletePost(
                        new DeletePostDto(
                            $post_id
                        )
                    );
                    $this->addFlash('notice', 'Post successfully deleted');

                    if($admin === 1) return new RedirectResponse($request->headers->get('referer'));
                    return $this->redirectToRoute('app_blog_post_list');

                } catch(\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('notice', 'Failed to delete post');
                }

                return new RedirectResponse($request->headers->get('referer'));
            } else {
                $error_string = "";
                foreach($form->getErrors() as $error) {
                    $error_string .= $error->getMessage();
                }
                $this->logger->error($error_string);
                $this->addFlash('notice', 'Failed to delete post');
                return new RedirectResponse($request->headers->get('referer'));
            }
        }

        return $this->render('blog/post/delete.button.html.twig', [
            'delete_form' => $form->createView(),
            'delete_post_id' => $post_id
        ]);
    }
}

