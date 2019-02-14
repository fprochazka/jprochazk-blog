<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

use App\Entity\Person;

class SecurityController extends AbstractController
{
    /**
      * @Route("/security/welcome", name="app_blog_security_welcome")
      */
    public function welcomeMessage(AuthenticationUtils $utils)
    {
        $lastAuthenticationError = $this->utils->getLastAuthenticationError();
        $lastUsername = $this->utils->getLastUsername();

        return $this->render('security/welcome.html.twig', [
            "last_username" => $lastUsername,
            "autherror" => $lastAuthenticationError,
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        if(is_null($current_user = $this->getUser())) {
            $user = new Person();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user, $form->get('plainPassword')->getData()
                    )
                );
                $user->setRole('ROLE_USER');

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main'
                );
            }

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_blog_post_list');
        }
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    	throw new \Exception('Logout failed');
    }
}
