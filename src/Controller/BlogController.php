<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Post;
use App\Entity\Person;
use App\Entity\Comment;
use App\Security\LoginFormAuthenticator;
use App\Entity\Survey;

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
                "id" => $i
            ];
        }
        return $posts;
    }

    private function getComments(int $post_id, int $start, int $count) {
        $unf_comments = $this->getPostByID($post_id)->getComments();
        $comments = array();
        $c = $start;
        foreach($unf_comments as $comment){
            if($c < $count) {
                $comments[$c] = [
                    "author" => $comment->getAuthor(),
                    "content" => $comment->getContent(),
                    "date" => $comment->getDate()->format('Y-m-d, H:i:s'),
                ];
            }
            ++$c;
        }
        return $comments;
    }

    private function getLatestSurvey() {
        $surveys = $this->getDoctrine()->getRepository(Survey::class)->findAll();
        $_survey;
        foreach($surveys as $survey) $_survey = $survey;
        return $_survey;
    }

    private function getSurvey(int $id) {
        return $this->getDoctrine()->getRepository(Survey::class)->find($id);
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

            if($current_user = $this->getUser()) {
                return $this->render('blog/post.html.twig', [
                    "post" => [
                        "title" => $post->getTitle(), 
                        "content" => $post->getContent(), 
                        "date" => $date, 
                        "author" => $post->getAuthor(),
                        "comments" => $this->getComments($post->getId(), 0, 10),
                        "num" => $num,
                    ],
                    "user_username" => $current_user->getUsername()
                ]);
            }
            return $this->render('blog/post.html.twig', [
                "post" => [
                    "title" => $post->getTitle(), 
                    "content" => $post->getContent(), 
                    "date" => $date, 
                    "author" => $post->getAuthor(),
                    "comments" => $this->getComments($post->getId(), 0, 10),
                    "num" => $num,
                ],
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

    /**
      * @Route("/post/{num<\d+>}/comment", name="app_blog_post_comment")
      */
    public function createComment($num, Request $request) {
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN' || $current_user->getRole() == 'ROLE_USER') {
                if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
                    $content = $request->request->get('content');
                    if(is_string($content)) {
                        $date = date_create_from_format('H:i:s Y-m-d', date('H:i:s Y-m-d'));
                        $comment = new Comment();
                        $post = $this->getPostByID($num);

                        $comment->setDate($date);
                        $comment->setAuthor($current_user->getUsername());
                        $comment->setContent($content);

                        $post->addComment($comment);

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($comment);
                        $entityManager->flush();

                        $responseData = [
                            'author' => $current_user->getUsername(),
                            'date' => $date->format('Y-m-d, H:i:s'),
                            'content' => $content
                        ];

                        return new JsonResponse(array(
                        'status' => 'OK',
                        'message' => $responseData),
                        200);
                    } else {
                        return new JsonResponse(array(
                        'status' => 'Error',
                        'message' => 'Content is not of type string'),
                        400);
                    }
                } else {
                    return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Not AJAX request'),
                    400);
                }
            }   else {
                return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Insufficient permissions'),
                400);
            }
        } else {
            return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'User not logged in'),
            400);
        }
    }

    /**
      * @Route("/survey", name="app_blog_survey")
      */
    public function renderSurvey() {
        $survey = $this->getLatestSurvey();
        if($this->getUser()) {
            $voted = $this->getUser()->hasVoted($survey->getId());
        } else {
            $voted = true;
        }

        return $this->render(
        'blog/blog.survey', [
            "survey" => [
                "id" => $survey->getId(),
                "title" => $survey->getTitle(), 
                "options" => $survey->getOptions(),
                "voted" => $voted
            ]
        ]);
    }

    /**
      * @Route("/survey/vote", name="app_blog_survey_vote")
      */
    public function surveyVote(Request $request) {
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN' || $current_user->getRole() == 'ROLE_USER') {
                if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {

                    $survey_id = (int)$request->request->get('survey_id');
                    $vote_id = (int)$request->request->get('vote_id');

                    $survey = $this->getSurvey($survey_id);

                    if(!$survey->isLocked()) {
                        if(!$current_user->hasVoted($survey_id)){

                            $survey->incrementVote($vote_id);
                            $current_user->addVote($survey_id, $vote_id);

                            $entityManager = $this->getDoctrine()->getManager();
                            //$entityManager->persist($survey);
                            //$entityManager->persist($current_user);
                            $entityManager->flush();

                            $responseData = [
                                'vote_id' => $vote_id
                            ];
                            return new JsonResponse(array(
                            'status' => 'OK',
                            'message' => $responseData),
                            200);
                        } else {
                            return new JsonResponse(array(
                            'status' => 'Error',
                            'message' => 'User has already voted'),
                            400);
                        }
                    } else {
                        return new JsonResponse(array(
                        'status' => 'Error',
                        'message' => 'Survey is locked'),
                        400);
                    }
                } else {
                    return new JsonResponse(array(
                    'status' => 'Error',
                    'message' => 'Not AJAX request'),
                    400);
                }
            }   else {
                return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Insufficient permissions'),
                400);
            }
        } else {
            return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'User not logged in'),
            400);
        }
    }
}