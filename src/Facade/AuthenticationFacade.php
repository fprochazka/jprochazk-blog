<?php

namespace App\Facade;

use App\DTO\RegistrationDto;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Security\CurrentUserProvider;
use App\Security\LoginFormAuthenticator;
use App\Security\SecurityUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthenticationFacade
{
    /** @var CurrentUserProvider */
    private $userProvider;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    private $roleRepo;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        CurrentUserProvider $userProvider,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepo
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userProvider = $userProvider;
        $this->entityManager = $entityManager;
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

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

}