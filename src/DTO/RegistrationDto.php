<?php


namespace App\DTO;


class RegistrationDto
{
    /** @var string */
    private $username;

    /** @var string */
    private $plainPassword;

    public function __construct
    (
        string $username,
        string $plainPassword
    )
    {
        $this->username = $username;
        $this->plainPassword = $plainPassword;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

}
