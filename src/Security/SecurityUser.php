<?php


namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUser implements UserInterface, \Serializable
{
    private $username;
    private $password;
    private $roles;

    public function __construct(User $user)
    {
        $this->username = $user->getUsername();
        $this->password = $user->getPassword();
        $this->roles = $user->getRoles();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->username,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->username,
            $this->password,
            ) = unserialize($serialized, array('allowed_classes' => false));
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
    }
}
