<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\Post;
use App\Form\PostFormType;

class PostController extends AbstractController
{

	/** @var PostRepository */
	private $postRepository;

	public function __construct(
		PostRepository $postRepository
	)
	{
		$this->postRepository = $postRepository;
	}

	/**
      * @Route("/{page<\d+>?1}", name="app_blog_post_list")
      */
    public function postList(int $page): Response
    {
        $posts = [];

        $c = 0;
        foreach($this->postRepository->findAllByOffsetCount($page, 10) as $post) {
            $posts[$c] = $post->toArray();
            if(strlen($posts[$c]["content"]) > 100) {
                $posts[$c]["content"] = substr($posts[$c]["content"], 0, 100).'...';
            }
            ++$c;
        }

        return $this->render('blog/post/list.html.twig', [
            "posts" => $posts,
        ]);
    }

    /**
      * @Route("/post/{id<\d+>}", name="app_blog_post_show")
      */
    public function showPost(int $id): Response {
        $_error = "";
        if($post = $this->postRepository->find($id)->toArray()) {

        	$current_user_username = ($this->getUser() != null) ? $this->getUser()->getUsername() : "guest";
            foreach($post["comments"] as $key => $value) {
                $post["comments"][$key]["canEdit"] = ($current_user_username == $post["comments"][$key]["author"]) ? true : false;
            }

            return $this->render('blog/post/show.html.twig', [
                "post" => $post
            ]);

        } else { $_error = "404"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/post/new", name="app_blog_post_new")
      */
    public function createPost(Request $request): Response {
        $_error = "";
        if(($current_user = $this->getUser()) !== null) {
            if($current_user->getRole() === 'ROLE_ADMIN') {
                $post = new Post();
                $form = $this->createForm(PostFormType::class, $post);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $post = $form->getData();
                    $date = new \DateTimeImmutable();

                    $post->setSubtime($date);
                    $post->setAuthor($current_user->getUsername());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($post);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_blog_post_show', ['id' => $post->getId()]);
                }

                return $this->render('blog/post/form.html.twig', [
                    'form' => $form->createView(),
                ]);
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/post/edit/{id<\d+>}", name="app_blog_post_edit")
      */
    public function editPost(int $id, Request $request): Response {
        $_error = "";
        if(($current_user = $this->getUser()) !== null) {
            if($current_user->getRole() === 'ROLE_ADMIN') {
                if(($post = $this->postRepository->find($id)) !== null) {
                    $form = $this->createForm(PostFormType::class, $post);

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $post = $form->getData();
                        $date = new \DateTimeImmutable();
                        $post->setSubtime($date);

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($post);
                        $entityManager->flush();

                        return $this->redirectToRoute('app_blog_post_show', ['id' => $post->getId()]);
                    }

                    return $this->render('blog/post/form.html.twig', [
                        'form' => $form->createView(),
                    ]);
                } else { $_error = "404"; }
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/post/delete/{id<\d+>}", name="app_blog_post_delete")
      */
    public function deletePost(int $id, Request $request): Response {
        $_error = "";
        if(($current_user = $this->getUser()) !== null) {
            if($current_user->getRole() === 'ROLE_ADMIN') {
                if($post = $this->postRepository->find($id)) {
                    $entityManager = $this->getDoctrine()->getManager();

                    foreach($post->getComments() as $comment) {
                       $post->removeComment($comment);
                       $entityManager->remove($comment);
                    }

                    $entityManager->remove($post);
                    $entityManager->flush();

                    $referer = (is_string($request->headers->get('referer')) && $request->headers->get('referer') !== null) ? $request->headers->get('referer') : "";
                    if ((explode("/", str_replace("http://", "", $referer)))[1] == "post") {
                        return $this->redirectToRoute('app_blog_post_list');
                    } else {
                        return new RedirectResponse($request->headers->get('referer'));
                    }
                } else { $_error = "404"; }
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }
}
