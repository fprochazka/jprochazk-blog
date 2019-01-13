<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            $title = ($post->getTitle()) . ($i);
            $posts[$i] = ["title" => $title, "content" => $post->getContent(), "link" => $this->getPostURL($i)];
        }

        return $this->render('blog/blog.html.twig', [
            "posts" => $posts,
        ]);
    }

    /**
      * @Route("/blog/post/{num<\d+>}", name="app_blog_post_show")
      */
    public function show($num) {
            $post = $this->getDoctrine()->getRepository(Post::class)->find($num);
            $fpost = ["title" => $post->getTitle(), "content" => $post->getContent()];

            return $this->render('blog/post.html.twig', [
                "post" => $fpost,
                "home" => $this->generateurl('app_blog_list')
            ]);
    }

    /**
      * @Route("/blog/new", name="app_blog_post_new")
      */
    public function createPost() {
        $entityManager = $this->getDoctrine()->getManager();

        $post = new Post();
        $temp_title = "some post #".$post->getId();
        $post->setTitle($temp_title);
        $post->setContent('some post content lorem ipsum dolor');

        $entityManager->persist($post);
        $entityManager->flush();
        return new Response('Saved new post with id '.$post->getId());
    }
}