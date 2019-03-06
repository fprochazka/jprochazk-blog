<?php

namespace App\Facade;

use App\Entity\Person;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Security;

class AuthenticationFacade
{
    /** @var Security */
    private $security;

    public function __construct(
        Security $security
    )
    {
        $this->security = $security;
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

}