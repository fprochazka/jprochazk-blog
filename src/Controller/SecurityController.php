<?php

namespace App\Controller;

use App\DTO\RegistrationDto;
use App\Facade\AuthenticationFacade;
use App\Security\SecurityUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
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
    public function register(Request $request, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): ?Response
    {
        $form = $this->createForm(RegistrationFormType::class, new User());

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user = $this->authFacade->register(
                    new RegistrationDto(
                        $form->get('username')->getData(),
                        $form->get('plainPassword')->getData()
                    )
                );

                $this->addFlash('notice', 'Registration successful!');

                return $guardHandler->authenticateUserAndHandleSuccess(
                    new SecurityUser($user),
                    $request,
                    $authenticator,
                    'main'
                );
            } else {
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error_container' => $form->getErrors()
                ]);
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
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
        if($this->getUser() === null) $this->addFlash('notice', 'Please use the login form in the sidebar.');
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
