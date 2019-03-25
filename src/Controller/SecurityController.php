<?php

namespace App\Controller;

use App\Facade\AuthenticationFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

use App\Entity\User;

class SecurityController extends AbstractController
{
    /** @var AuthenticationFacade */
    private $authFacade;

    public function __construct(
        AuthenticationFacade $authFacade
    )
    {
        $this->authFacade = $authFacade;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, GuardAuthenticatorHandler $guardHandler): ?Response
    {
        $response = $this->authFacade->register($request);

        if($response['status'] === 200) {
            return $guardHandler->authenticateUserAndHandleSuccess(
                $response['data']['user'],
                $request,
                $response['data']['authenticator'],
                $response['data']['providerKey']
            );
        } elseif($response['status'] === 500) {
            return $this->render('registration/register.html.twig', [
                'registrationForm' => $this->authFacade->getRegistrationFormView(),
                'error_container' => $response['message']
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $this->authFacade->getRegistrationFormView(),
            'error_container' => null
        ]);
    }

    /**
      * @Route("/security/welcome", name="app_blog_security_welcome")
      */
    public function welcomeMessage(AuthenticationUtils $utils): Response
    {
        $lastAuthenticationError = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        return $this->render('security/welcome.html.twig', [
            'last_username' => $lastUsername,
            'autherror' => $lastAuthenticationError,
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(): Response
    {
        return $this->redirectToRoute('app_blog_post_list');
    }
    
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): Response
    {
        return $this->redirectToRoute('app_blog_post_list');
    }
}
