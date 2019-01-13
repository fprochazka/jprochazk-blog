<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    private function getPostURL($page_num) {
        return $this->generateurl('app_blog_list', array('page' => $page_num));
    }

    /**
      * @Route("/blog/{page<\d+>?1}", name="app_blog_list")
      */
    public function list($page)
    {
        $posts = array( 

            1 => array(
                "title" => "first post", 
                "link" => $this->getPostURL(1), 
                "content" => "first post content lorem ipsum dolor"
            ),
            2 => array(
                "title" => "second post", 
                "link" => $this->getPostURL(2), 
                "content" => "second post content lorem ipsum"
            ),
            3 => array(
                "title" => "third post", 
                "link" => $this->getPostURL(3), 
                "content" => "third post content lorem ipsum dolor"
            )

        );
    	return $this->render('blog/blog.html.twig', [
            "posts" => $posts,
    	]);
    }
}