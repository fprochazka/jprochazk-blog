<?php

namespace App\Security;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        $referer = (is_string($request->headers->get('referer')) && $request->headers->get('referer') !== null) ? $request->headers->get('referer') : '';

        $urlArray = explode('/', str_replace('http://', '', $referer));
        if($urlArray[1] == 'post' && $urlArray[2] != 'new' && $urlArray[1] != 'admin') {
            return new RedirectResponse($referer);
        }

        return new RedirectResponse($this->router->generate('app_blog_post_list'));
    }
}
