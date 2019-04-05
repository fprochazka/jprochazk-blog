<?php


namespace App\Security;

namespace App\Security;

use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class SecurityUserProvider implements UserProviderInterface
{
    /** @var UserRepository */
    private $userRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(UserRepository $userRepository, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function loadUserByUsername($username): SecurityUser
    {
        return $this->fetchUser($username);
    }

    public function refreshUser(UserInterface $user): SecurityUser
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUsername();

        $this->logger->info('Username (Refresh): '.$username);

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return SecurityUser::class === $class;
    }

    private function fetchUser(string $username): SecurityUser
    {
        if (null === ($user = $this->userRepository->findOneBy(['username' => $username]))) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return new SecurityUser($user);
    }
}
