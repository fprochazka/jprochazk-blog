<?php


namespace App\Controller;


use App\Facade\AdminFacade;
use App\Facade\AuthenticationFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    /** @var AdminFacade */
    private $adminFacade;

    /** @var AuthenticationFacade  */
    private $authFacade;

    public function __construct
    (
        AdminFacade $adminFacade,
	    AuthenticationFacade $authFacade
	)
	{
        $this->adminFacade = $adminFacade;
        $this->authFacade = $authFacade;
    }

    /**
     * @Route("/admin/user", name="app_blog_admin_users")
     */
    public function adminUserList(Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $data = $this->adminFacade->getAdminUserData();

        return $this->render('blog/admin/admin.users.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/post", name="app_blog_admin_posts")
     */
    public function adminPostList(Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $data = $this->adminFacade->getAdminPostData();

        return $this->render('blog/admin/admin.posts.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/survey", name="app_blog_admin_surveys")
     */
    public function adminSurveyList(Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $data = $this->adminFacade->getAdminSurveyData();

        return $this->render('blog/admin/admin.surveys.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin", name="app_blog_admin")
     */
    public function showAdmin(Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        return $this->render('blog/admin/admin.html.twig');
    }
}

