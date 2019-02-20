<?php

namespace App\Security;

use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class LoginFormAuthenticator
 * @package App\Security
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request): bool
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request): array
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        if($session = $request->getSession()) {
            $session->set(
                Security::LAST_USERNAME,
                $credentials['username']
            );
        } else {
            throw new \LogicException("could not get session in LoginFormAuthenticator");
        }

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider): Person
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(Person::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        /** @var Person $user */
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        if(!$referer) {
            throw new \LogicException("could not get referer CONTROLLER: POST, deletePost()");
        } else if($referer) {
            $urlArray = explode("/", str_replace("http://", "", $referer));
            if($urlArray[1] == "post") {
                if($urlArray[2] != "new") {
                    return new RedirectResponse($referer);
                }
            }
        }

        return new RedirectResponse($this->router->generate('app_blog_post_list'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        if(!$referer) {
            throw new \LogicException("could not get referer CONTROLLER: POST, deletePost()");
        } else if($referer) {
            $urlArray = explode("/", str_replace("http://", "", $referer));
            if($urlArray[1] == "post") {
                if($urlArray[2] != "new") {
                    return new RedirectResponse($referer);
                }
            }
        }

        return new RedirectResponse($this->router->generate('app_blog_post_list'));
    }

    protected function getLoginUrl(): string
    {
        return $this->router->generate('app_login');
    }
}
