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

    public function checkAuthentication(): ?RedirectResponse
    {
        /** @var Person $user */
        $user = $this->security->getUser();
        if($this->security->getUser() == null) return new RedirectResponse('app_blog_error', 302, ['msg' => 'auth']);
        if($user->getRole() !== 'ROLE_ADMIN') return new RedirectResponse('app_blog_error', 302, ['msg' => '403']);
        else return null;
    }

}