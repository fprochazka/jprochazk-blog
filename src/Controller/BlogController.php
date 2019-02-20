<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\Post;
use App\Entity\Person;
use App\Entity\Survey;
use App\Entity\SurveyOption;

class BlogController extends AbstractController
{
    /**
     * @Route("/admin", name="app_blog_admin")
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function showAdmin(Request $request) {
        $_error = "";
        $tab = $request->query->get('p');
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                if(!$tab) {
                    return $this->redirectToRoute("app_blog_admin", ['p' => "users"]);
                } else {
                    //users + "survey: option" voted on by the user
                    if($tab == "users") {
                        $users = [];
                        foreach($this->getDoctrine()->getRepository(Person::class)->findAll() as $user) {
                            $votes = [];
                            // intentionally stupid code
                            if($user->getVotes()) {
                                foreach($user->getVotes() as $key => $value) {
                                    $survey_name = $this->getDoctrine()->getRepository(Survey::class)->find($key)->getTitle();
                                    $option_name = $this->getDoctrine()->getRepository(SurveyOption::class)->find($value)->getTitle();
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
                        foreach($this->getDoctrine()->getRepository(Post::class)->findAll() as $post) {
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
                        foreach($this->getDoctrine()->getRepository(Survey::class)->findAll() as $survey) {
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
     *
     * @param Request $request
     * @return RedirectResponse|Response
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
}