<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Post;

class BlogController extends AbstractController
{
    private function getPostURL($post_num) {
        return $this->generateurl('app_blog_post_show', array('num' => $post_num));
    }

    /**
      * @Route("/blog/{page<\d+>?1}", name="app_blog_list")
      */
    public function list($page)
    {
        $posts = array();
        for($i = $page; $i < $page+10; ++$i){
            $post = $this->getDoctrine()->getRepository(Post::class)->find($i);
            if(!$post) {
                continue;
            }
            $posts[$i] = ["title" => $post->getTitle(), "content" => $post->getContent(), "link" => $this->getPostURL($i)];
        }

        return $this->render('blog/blog.html.twig', [
            "posts" => $posts,
            "new" => $this->generateurl('app_blog_post_new'),
        ]);
    }

    /**
      * @Route("/blog/post/{num<\d+>}", name="app_blog_post_show")
      */
    public function show($num) {
            $post = $this->getDoctrine()->getRepository(Post::class)->find($num);
            $date = $post->getSubtime()->format('Y-m-d');
            $fpost = ["title" => $post->getTitle(), "content" => $post->getContent(), "date" => $date];

            return $this->render('blog/post.html.twig', [
                "post" => $fpost,
                "home" => $this->generateurl('app_blog_list')
            ]);
    }

    /**
      * @Route("/blog/new", name="app_blog_post_new")
      */
    public function createPost(Request $request) {
        $post = new Post();
        $form = $this->createFormBuilder($post)
        ->add('title', TextType::class)
        ->add('content', TextType::class)
        ->add('save', SubmitType::class, ['label' => 'Submit post'])
        ->getForm();

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

        return $this->render('blog/post.new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}