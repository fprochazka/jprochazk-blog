<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     *
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="Users")
     */
    private $Roles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SurveyOption", inversedBy="users")
     */
    private $votes;

    public function __construct(string $username = '', string $password = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->Roles = new ArrayCollection();
        $this->votes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach($this->Roles as $role) {
            $roles[] = $role->getName();
        }
        return $roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->Roles->contains($role)) {
            $this->Roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->Roles->contains($role)) {
            $this->Roles->removeElement($role);
        }

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return null;
    }

    /**
     * @return Collection|Survey[]
     */
    public function getVotes(): Collection
    {
        $votedOn = $this->votes;
        $votes = [];
        foreach($votedOn as $vote)
        {
            $votes[] = $vote->getSurvey();
        }

        return $this->votes;
    }

    public function hasVoted(int $id): bool
    {
        $options_voted_on = $this->votes;
        foreach($options_voted_on as $option)
        {
            $temp_id = $option->getSurvey()->getId();
            if($temp_id === $id) {
                return true;
            }
        }

        return false;
    }

    public function addVote(SurveyOption $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
        }

        return $this;
    }

    public function removeVote(SurveyOption $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
        }

        return $this;
    }
}
