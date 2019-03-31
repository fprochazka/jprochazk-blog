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
     * @Route("/admin", name="app_blog_admin")
     */
    public function showAdmin(Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $tab = $request->query->get('p');
        if($tab === null) {
            return $this->redirectToRoute('app_blog_admin', ['p' => 'users']);
        }

        $data = $this->adminFacade->getAdminData();

        return $this->render('blog/admin.html.twig', [
            'tab' => $tab,
            'data' => $data,
        ]);
    }
}

