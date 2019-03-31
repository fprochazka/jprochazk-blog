<?php

namespace App\Facade;

use App\DTO\RegistrationDto;
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
    private $userProvider;

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
        CurrentUserProvider $userProvider,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        RoleRepository $roleRepo
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->authenticator = $authenticator;
        $this->userProvider = $userProvider;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->roleRepo = $roleRepo;
    }

    public function getAuthenticationError(): ?string
    {
        /** @var User $user */
        $user = $this->userProvider->getUser();
        $authenticationError = null;

        if($user === null) $authenticationError = 'auth';
        else if($user->hasRole($this->roleRepo->getAdminRole())) $authenticationError = '403';

        return $authenticationError;
    }

    public function register(RegistrationDto $dto): User
    {
        $user = new User();
        $user_role = $this->roleRepo->getUserRole();

        $username = $dto->getUsername();
        $plainPassword = $dto->getPlainPassword();

        $user
            ->setUsername($username)
            ->setPassword($this->passwordEncoder->encodePassword(
                new SecurityUser(new User()), $plainPassword))
            ->addRole($user_role)
        ;

        //$this->entityManager->persist($user);
        //$this->entityManager->flush();

        return $user;
    }

}