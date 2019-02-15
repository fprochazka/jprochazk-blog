<?php

namespace App\Security;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request) 
    {
        $referer = $request->headers->get('referer');
        $urlArray = explode("/", str_replace("http://", "", $referer));
        if($urlArray[1] == "post") {
            if($urlArray[2] != "new") {
                return new RedirectResponse($referer);
            }
        }
        return new RedirectResponse($this->router->generate('app_blog_post_list'));
    }
}
