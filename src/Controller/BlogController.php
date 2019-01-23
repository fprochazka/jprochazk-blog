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
use App\Entity\Person;
use App\Security\LoginFormAuthenticator;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class BlogController extends AbstractController
{
    private function getPostByID(int $id) {
        return $this->getDoctrine()->getRepository(Post::class)->find($id);
    }

    private function restrictText(string $content, int $length) {
        if(strlen($content) > $length) {
            return substr($content, 0, $length).'...';
        } else {
            return $content;
        }
    }

    private function getPosts(int $start, int $count) {
        $posts = array();
        for($i = $start; $i < $start+$count; ++$i){
            if($post = $this->getPostByID($i)) $posts[$i] = [
                "title" => $post->getTitle(), 
                "content" => $this->restrictText($post->getContent(), 50), 
                "id" => $i,
            ];
        }
        return $posts;
    }

    /**
      * @Route("/{page<\d+>?1}", name="app_blog_list")
      */
    public function list($page)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $current_user = $this->getUser();
        return $this->render('blog/blog.html.twig', [
            "posts" => $this->getPosts($page, 10),
            "user_username" => $current_user->getUsername()
        ]);
    }

    /**
      * @Route("/post/{num<\d+>}", name="app_blog_post_show")
      */
    public function show($num) {
            $post = $this->getPostByID($num);
            $date = $post->getSubtime()->format('Y-m-d');

            return $this->render('blog/post.html.twig', [
                "post" => ["title" => $post->getTitle(), "content" => $post->getContent(), "date" => $date],
                "home" => $this->generateurl('app_blog_list')
            ]);
    }

    /**
      * @Route("/new", name="app_blog_post_new")
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