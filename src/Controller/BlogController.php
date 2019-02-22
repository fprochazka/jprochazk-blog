<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use App\Repository\PostRepository;
use App\Repository\SurveyOptionRepository;
use App\Repository\SurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{

	/** @var PersonRepository */
	private $personRepository;

	/** @var SurveyRepository */
	private $surveyRepository;

	/** @var SurveyOptionRepository */
	private $surveyOptionRepository;

	/** @var PostRepository */
	private $postRepository;

	public function __construct(
		PostRepository $postRepository,
		PersonRepository $personRepository,
		SurveyRepository $surveyRepository,
		SurveyOptionRepository $surveyOptionRepository
	)
	{
		$this->personRepository = $personRepository;
		$this->surveyRepository = $surveyRepository;
		$this->surveyOptionRepository = $surveyOptionRepository;
		$this->postRepository = $postRepository;
	}

	/**
     * @Route("/admin", name="app_blog_admin")
     */
    public function showAdmin(Request $request): Response {
        if($this->getUser() === null) return $this->redirectToRoute('app_blog_error', ['msg' => 'auth']);
        if($this->getUser()->getRole() !== 'ROLE_ADMIN') return $this->redirectToRoute('app_blog_error', ['msg' => '403']);

        $tab = $request->query->get('p');
        switch($tab) {
            case null:
                return $this->redirectToRoute("app_blog_admin", ['p' => "users"]);
            case "users":
                $users = [];
                foreach($this->personRepository->findAll() as $user) {
                    $users[] = $user->toArray();
                }
                return $this->render("blog/admin.html.twig", [
                    'tab' => $tab,
                    'users' => $users,
                ]);
            case "posts":
                $posts = [];
                foreach($this->postRepository->findAll() as $post) {
                    $posts[] = $post->toArray();
                }
                return $this->render("blog/admin.html.twig", [
                    'tab' => $tab,
                    'posts' => $posts,
                ]);
            case "surveys":
                $surveys = [];
                foreach($this->surveyRepository->findAll() as $survey) {
                    $surveys[] = $survey->toArray();
                }
                return $this->render("blog/admin.html.twig", [
                    'tab' => $tab,
                    'surveys' => $surveys,
                ]);
            default:
                return $this->render("blog/admin.html.twig", [
                    'tab' => "error"
                ]);
        }
    }

    /**
     * @Route("/error", name="app_blog_error")
     */
    public function showError(Request $request): Response {
        $msg = $request->query->get("msg");

        switch($msg) {
            case "auth":
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Login to access this page',
                ]);
            case "403":
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Insufficient permissions',
                ]);
            case "404":
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Page not found.',
                ]);
            default:
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Unknown error',
                ]);
        }
    }
}

