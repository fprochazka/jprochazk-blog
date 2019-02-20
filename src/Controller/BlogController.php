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
        $_error = "";
        $tab = $request->query->get('p');
        if(($current_user = $this->getUser()) != null) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                if($tab == null) {
                    return $this->redirectToRoute("app_blog_admin", ['p' => "users"]);
                } else {
                    //users + "survey: option" voted on by the user
                    if($tab == "users") {
                        $users = [];
                        foreach($this->personRepository->findAll() as $user) {
                            $votes = [];
                            // intentionally stupid code
                            if($user->getVotes()) {
                                foreach($user->getVotes() as $key => $value) {
                                    $survey_name = $this->surveyRepository->find($key)->getTitle();
                                    $option_name = $this->surveyOptionRepository->find($value)->getTitle();
                                    $votes[$survey_name] = $option_name;
                                }
                            }

                            $users[] = [
                                'id' => $user->getId(),
                                'name' => $user->getUsername(),
                                'role' => $user->getRole(),
                                'votes' => $votes
                            ];

                            $votes = [];
                        }
                        return $this->render("blog/admin.html.twig", [
                            'tab' => $tab,
                            'users' => $users,
                        ]);
                    }

                    //posts standalone
                    elseif($tab == "posts") {
                        $posts = [];
                        foreach($this->postRepository->findAll() as $post) {
                            $posts[] = $post->toArray();
                        }
                        return $this->render("blog/admin.html.twig", [
                            'tab' => $tab,
                            'posts' => $posts,
                        ]);
                    }

                    //surveys+options
                    elseif($tab == "surveys") {
                        $surveys = [];
                        foreach($this->surveyRepository->findAll() as $survey) {
                            $surveys[] = $survey->toArray();
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

    /**
     * @Route("/error", name="app_blog_error")
     */
    public function showError(Request $request): Response {
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
}

