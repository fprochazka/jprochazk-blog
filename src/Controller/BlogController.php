<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Post;
use App\Entity\Person;
use App\Security\LoginFormAuthenticator;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
        for($i = $start; $i < $start+$count; ++$i) {
            if($post = $this->getPostByID($i)) $posts[$i] = [
                "title" => $post->getTitle(), 
                "content" => $this->restrictText($post->getContent(), 50), 
                "id" => $i,
            ];
        }
        return $posts;
    }

    private function getComments(int $post_id, int $start, int $count) {
        $post = getPostByID($post_id);
        $comments = $post->getComments();
    }

    /**
      * @Route("/{page<\d+>?1}", name="app_blog_list")
      */
    public function list($page, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        if(!is_null($current_user = $this->getUser())) return $this->render('blog/blog.html.twig', [
            "posts" => $this->getPosts($page, 10),
            "user_username" => $current_user->getUsername()
        ]);
        return $this->render('blog/blog.html.twig', [
            "posts" => $this->getPosts($page, 10),
            "last_username" => $lastUsername,
            "error" => $error
        ]);
    }

    /**
      * @Route("/post/{num<\d+>}", name="app_blog_post_show")
      */
    public function show($num, AuthenticationUtils $authenticationUtils) {
        if($post = $this->getPostByID($num)) {
            $date = $post->getSubtime()->format('Y-m-d');
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();

            if(!is_null($current_user = $this->getUser())) return $this->render('blog/post.html.twig', [
                "post" => ["title" => $post->getTitle(), "content" => $post->getContent(), "date" => $date, "author" => $post->getAuthor()],
                "user_username" => $current_user->getUsername()
            ]);
            return $this->render('blog/post.html.twig', [
                "post" => ["title" => $post->getTitle(), "content" => $post->getContent(), "date" => $date, "author" => $post->getAuthor()],
                "user_username" => "guest",
                "last_username" => $lastUsername, 
                "error" => $error
            ]);
        } else {
            return new Response('Page not found. <a href="/">Home</a>', Response::HTTP_NOT_FOUND);
        }
    }

    /**
      * @Route("/new", name="app_blog_post_new")
      */
    public function createPost(Request $request) {
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                $post = new Post();
                $form = $this->createFormBuilder($post)
                ->add('title', TextType::class)
                ->add('content', TextareaType::class)
                ->add('save', SubmitType::class, ['label' => 'Submit post'])
                ->getForm();

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

                return $this->render('blog/post.new.html.twig', [
                    'form' => $form->createView(),
                ]);
            } else {
                return $this->redirectToRoute('app_blog_list');
            }
        } else {
            return $this->redirectToRoute('app_blog_list');
        }
    }

}