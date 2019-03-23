<?php

namespace App\Facade;

use App\Entity\Person;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class AuthenticationFacade
{
    /** @var Security */
    private $security;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var LoginFormAuthenticator */
    private $authenticator;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        Security $security,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->authenticator = $authenticator;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function getAuthenticationError(): ?string
    {
        /** @var Person $user */
        $user = $this->security->getUser();
        $authenticationError = null;

        if($this->security->getUser() == null) $authenticationError = 'auth';
        else if($user->getRole() !== 'ROLE_ADMIN') $authenticationError = '403';

        return $authenticationError;
    }

    private function getRegistrationForm(?Person $options = null): FormInterface
    {
        if($options === null) {
            $user = new Person();
            $user->setUsername('');
            $user->setPassword('');
        } else {
            $user = $options;
        }
        return $this->formFactory->create(RegistrationFormType::class, $user);
    }

    public function getRegistrationFormView(?Person $options = null): FormView
    {
        return $this->getRegistrationForm($options)->createView();
    }

    private function saveUser(Person $user): bool
    {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        } catch(ORMException $e) {
            return false;
        }

    }

    public function register(Request $request): array
    {
        $response = [
            'status' => 0
        ];

        $user = new Person();
        $user->setUsername('');
        $user->setPassword('');

        $form = $this->getRegistrationForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user->setPassword(
                    $this->passwordEncoder->encodePassword(
                        $user, $form->get('plainPassword')->getData()
                    )
                );
                $user->setRole('ROLE_USER');

                if($this->saveUser($user)) {
                    $response['status'] = 200;
                    $response['data'] = [
                        'user' => $user,
                        'authenticator' => $this->authenticator,
                        'providerKey' => 'main'
                    ];
                } else {
                    $response['status'] = 500;
                    $response['message'] = 'Could not save user';
                }
            } else {
                $response['status'] = 500;
                $errors = $form->getErrors(true);
                /** @var FormError $error */
                foreach($errors as $error) {
                    $response['message'][] = $error->getMessage();
                }
            }
        }

        return $response;
    }

}