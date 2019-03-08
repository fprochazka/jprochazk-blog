<?php

namespace App\Controller;

use App\Facade\AdminFacade;
use App\Facade\AuthenticationFacade;
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
    /** @var AuthenticationFacade */
	private $authFacade;

	/** @var AdminFacade  */
	private $adminFacade;

	public function __construct(
	    AuthenticationFacade $authFacade,
        AdminFacade $adminFacade
	)
	{
	    $this->authFacade = $authFacade;
	    $this->adminFacade = $adminFacade;
	}

	/**
     * @Route("/admin", name="app_blog_admin")
     */
    public function showAdmin(Request $request): Response {
        if(($authenticationError = $this->authFacade->getAuthenticationError()) !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $response = $this->adminFacade->adminDataList($request);
        if($response['tab'] === null) {
            return $this->redirectToRoute('app_blog_admin', ['p' => 'users']);
        }

        return $this->render('blog/admin.html.twig', [
            'tab' => $response['tab'],
            'data' => $response['data'],
        ]);
    }

    /**
     * @Route("/error", name="app_blog_error")
     */
    public function showError(Request $request): Response {
        $msg = $request->query->get('msg');

        switch($msg) {
            case 'auth':
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Login to access this page',
                ]);
            case '403':
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Insufficient permissions',
                ]);
            case '404':
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

