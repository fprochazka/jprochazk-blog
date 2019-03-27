<?php


namespace App\Security;


use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CurrentUserProvider
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserRepository */
    private $userRepo;

    /** @var User|null */
    private $currentUser;

    public function __construct(TokenStorageInterface $tokenStorage, UserRepository $userRepo)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userRepo = $userRepo;
    }
    public function getUser(): ?User
    {
        if (!$this->currentUser) {
            $this->currentUser = $this->fromToken($this->tokenStorage->getToken());
        }

        return $this->currentUser;
    }
    public function fromToken(TokenInterface $token): ?User
    {
        if (!$token || !$token->getUser() instanceof SecurityUser) {
            return null;
        }

        return $this->userRepo->findOneByUsername($token->getUsername());
    }
}