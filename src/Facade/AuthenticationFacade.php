<?php

namespace App\Facade;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\RoleRepository;
use App\Security\CurrentUserProvider;
use App\Security\LoginFormAuthenticator;
use App\Security\SecurityUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthenticationFacade
{
    /** @var CurrentUserProvider */
    private $security;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var LoginFormAuthenticator */
    private $authenticator;

    private $roleRepo;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        CurrentUserProvider $security,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        RoleRepository $roleRepo
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->authenticator = $authenticator;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->roleRepo = $roleRepo;
    }

    public function getAuthenticationError(): ?string
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $authenticationError = null;

        if($this->security->getUser() == null) $authenticationError = 'auth';
        else if(array_search('ROLE_ADMIN', $user->getRoles(), true)) $authenticationError = '403';

        return $authenticationError;
    }

    private function getRegistrationForm(?User $options = null): FormInterface
    {
        if($options === null) {
            $user = new User();
            $user->setUsername('');
            $user->setPassword('');
        } else {
            $user = $options;
        }
        return $this->formFactory->create(RegistrationFormType::class, $user);
    }

    public function getRegistrationFormView(?User $options = null): FormView
    {
        return $this->getRegistrationForm($options)->createView();
    }

    private function saveUser(User $user): bool
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

        $user = new User();
        $user->setUsername('');
        $user->setPassword('');

        $form = $this->getRegistrationForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $username = $form->get('username')->getData();
                $plainPassword = $form->get('plainPassword')->getData();

                $securityUser = new SecurityUser($user);
                $user->setPassword(
                    $this->passwordEncoder->encodePassword(
                        new SecurityUser(new User()), $form->get('plainPassword')->getData()
                    )
                );

                $user_role = $this->roleRepo->getUserRole();
                $user->addRole($user_role);

                if($this->saveUser($user)) {
                    $response['status'] = 200;
                    $response['data'] = [
                        'user' => new SecurityUser($user),
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