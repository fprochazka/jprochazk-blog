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
use App\Form\SurveyType;

use App\Entity\Post;
use App\Entity\Person;
use App\Entity\Comment;
use App\Security\LoginFormAuthenticator;
use App\Entity\Survey;
use App\Entity\SurveyOption;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class BlogController extends AbstractController
{
    private function getAllPosts() {
        return $this->getDoctrine()->getRepository(Post::class)->findAll();
    }

    private function getAllUsers() {
        return $this->getDoctrine()->getRepository(Person::class)->findAll();
    }

    private function getAllSurveys() {
        return $this->getDoctrine()->getRepository(Survey::class)->findAll();
    }

    private function getPostByID(int $id) {
        return $this->getDoctrine()->getRepository(Post::class)->find($id);
    }

    private function getSurveyById(int $id) {
        return $this->getDoctrine()->getRepository(Survey::class)->find($id);
    }

    private function getOptionById(int $id) {
        return $this->getDoctrine()->getRepository(SurveyOption::class)->find($id);
    }

    private function restrictText(string $content, int $length) {
        if(strlen($content) > $length) {
            return substr($content, 0, $length).'...';
        } else {
            return $content;
        }
    }

    /*private function getPostsByAuthor(string $author) {
        $posts = array();
        foreach($this->getAllPosts() as $post) {
            if($post->getAuthor() == $author) {
                $posts[] = [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'date' => $post->getSubtime()->format('H:i:s Y-m-d'),
                    'author' => $post->getAuthor()
                ];
            }
        }
    }*/

    private function getPosts(int $start, int $count = 0) {
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
        $_survey = null;
        foreach($surveys as $survey) $_survey = $survey;
        return $_survey;
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
        $_error;
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
        } else { $_error = "404"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/new", name="app_blog_post_new")
      */
    public function createPost(Request $request) {
        $_error;
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
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/survey/new", name="app_blog_survey_new")
      */
    public function createSurvey(Request $request) {
        $_error;
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                $survey = new Survey();
                $form = $this->createForm(SurveyType::class, $survey);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $survey = $form->getData();
                    
                    $options = $survey->getOptions();
                    $survey->resetOptions();
                    foreach($options as $option) {
                        $option->setVotes(0);
                        $survey->addOption($option);
                        $entityManager->persist($option);
                    }

                    $survey->unlock();

                    $entityManager->persist($survey);
                    $entityManager->flush();

                    return $this->redirectToRoute("app_blog_list");
                }

                return $this->render('blog/blog.survey.new.twig', [
                    'form' => $form->createView(),
                ]);
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
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
        if($survey != null) {
            $voted = true;
            if($current_user = $this->getUser()) {
                $voted = $current_user->hasVoted($survey->getId());
            }

            $count = 1;
            $_options = $survey->getOptions();
            foreach($_options as $option) {
                $options[$count] = [
                    "name" => $option->getTitle(),
                    "votes" => $option->getVotes(),
                    "id" => $option->getId(),
                ];
                ++$count;
            }

            return $this->render(
            'blog/blog.survey.twig', [
                "survey" => [
                    "id" => $survey->getId(),
                    "title" => $survey->getTitle(), 
                    "options" => $options,
                    "voted" => $voted
                ]
            ]);
        } else {
            return new Response("");
        }
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

                    $survey = $this->getSurveyById($survey_id);

                    if(!$survey->isLocked()) {
                        if(!$current_user->hasVoted($survey_id)){

                            $survey->getOptionById($vote_id)->incrementVote();
                            $current_user->addVote($survey_id, $vote_id);

                            $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/error", name="app_blog_error")
     */
    public function showError(Request $request) {
        $msg = $request->query->get("msg");
        if($msg == "perm") {
            return $this->render('blog/error.html.twig', [
                'msg' => 'Insufficient permissions',
            ]);
        } 
        elseif($msg == "auth") {
            return $this->render('blog/error.html.twig', [
                'msg' => 'Login to access this page',
            ]);
        }
        elseif($msg == "404") {
            return $this->render('blog/error.html.twig', [
                'msg' => 'Page not found.',
            ]);
        }
        else {
            return $this->render('blog/error.html.twig', [
                'msg' => 'Unknown error',
            ]);
        }
    }

    /**
     * @Route("/admin", name="app_blog_admin")
     */
    public function showAdmin(Request $request) {
        $_error;
        $tab = $request->query->get('p');
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                if(!$tab) {
                    return $this->redirectToRoute("app_blog_admin", ['p' => "users"]);
                } else {
                    //users + their posts
                    if($tab == "users") {
                        $users;
                        foreach($this->getAllUsers() as $user) {
                            $votes = [];
                            if($user->getVotes()) {
                                foreach($user->getVotes() as $key => $value) {
                                    $survey_name = $this->getSurveyById($key)->getTitle();
                                    $option_name = $this->getOptionById($value)->getTitle();
                                    $votes[$survey_name] = $option_name;
                                }
                            }
                            $users[] = [
                                'id' => $user->getId(),
                                'name' => $user->getUsername(),
                                'role' => $user->getRole(),
                                'votes' => $votes
                            ];
                            $votes = array();
                        }
                        return $this->render("blog/admin.html.twig", [
                            'tab' => $tab,
                            'users' => $users,
                        ]);
                    } 
                    //posts standalone
                    elseif($tab == "posts") {
                        $posts;
                        foreach($this->getAllPosts() as $post) {
                            $comments;
                            foreach($post->getComments() as $comment) {
                                $comments[] = [
                                    'id' => $comment->getId(),
                                    'content' => $comment->getContent(),
                                    'date' => $comment->getDate()->format('H:i:s, Y-m-d'),
                                    'author' => $comment->getAuthor(),
                                ];
                            }

                            $posts[] = [
                                'id' => $post->getId(),
                                'title' => $post->getTitle(),
                                'content' => $post->getContent(),
                                'date' => $post->getSubtime()->format('H:i:s Y-m-d'),
                                'author' => $post->getAuthor(),
                                'comments' => $comments,
                            ];

                            $comments = array();
                        }
                        return $this->render("blog/admin.html.twig", [
                            'tab' => $tab,
                            'posts' => $posts,
                        ]);
                    } 
                    //surveys
                    elseif($tab == "surveys") {
                        $surveys = [];
                        foreach($this->getAllSurveys() as $survey) {
                            $options = [];
                            foreach($survey->getOptions() as $option) {
                                $options[] = [
                                    "id" => $option->getId(),
                                    "name" => $option->getTitle(),
                                    "votes" => $option->getVotes(),
                                ];
                            }

                            $surveys[] = [
                                "id" => $survey->getId(),
                                "title" => $survey->getTitle(), 
                                "options" => $options
                            ];

                            $options = [];
                        }
                        return $this->render("blog/admin.html.twig", [
                            'tab' => $tab,
                            'surveys' => $surveys,
                        ]);
                    }

                    else {
                        return $this->render("blog/admin.html.twig", [
                            'tab' => "error"
                        ]);
                    }
                }
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }
        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }
}