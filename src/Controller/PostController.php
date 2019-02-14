<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Post;
use App\Form\PostFormType;

class PostController extends AbstractController
{

    /**
      * @Route("/{page<\d+>?1}", name="app_blog_list")
      */
    public function postList($page)
    {
        $posts = [];

        $c = 0;
        foreach($this->getDoctrine()->getRepository(Post::class)->findAllByOffsetCount($page, 10) as $post) {
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
    public function showPost($id) {
        $_error;
        if($post = $this->getDoctrine()->getRepository(Post::class)->find($id)->toArray()) {

            foreach($post["comments"] as $key => $value) {
                $authorname = $post["comments"][$key]["canEdit"] = ($this->getUser()->getUsername() == $post["comments"][$key]["author"]) ? true : false;
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
    public function createPost(Request $request) {
        $_error;
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                $post = new Post();
                $form = $this->createForm(PostFormType::class, $post);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $post = $form->getData();
                    $date = date_create_from_format('Y-m-d', date('Y-m-d'));

                    $post->setSubtime($date);
                    $post->setAuthor($current_user->getUsername());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($post);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_blog_post_show', ['num' => $post->getId()]);
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
    public function editPost($id, Request $request) {
        $_error;
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                if($post = $this->getDoctrine()->getRepository(Post::class)->find($id)) {
                    $form = $this->createForm(PostFormType::class, $post);

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $post = $form->getData();
                        $date = date_create_from_format('Y-m-d', date('Y-m-d'));
                        $post->setSubtime($date);

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($post);
                        $entityManager->flush();

                        return $this->redirectToRoute('app_blog_post_show', ['num' => $post->getId()]);
                    }

                    return $this->render('blog/post/form.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/post/delete/{id<\d+>}", name="app_blog_post_edit")
      */
    public function deletePost($id, Request $request) {
        $_error;
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                if($post = $this->getDoctrine()->getRepository(Post::class)->find($id)) {
                    $form = $this->createForm(PostFormType::class, $post);

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $post = $form->getData();
                        $date = date_create_from_format('Y-m-d', date('Y-m-d'));
                        $post->setSubtime($date);

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($post);
                        $entityManager->flush();

                        return $this->redirectToRoute('app_blog_post_show', ['num' => $post->getId()]);
                    }

                    return $this->render('blog/post/form.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }
}
