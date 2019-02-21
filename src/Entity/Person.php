<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class Person implements UserInterface, \Serializable
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
     * @ORM\Column(type="string", length=255, unique=true)
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
     * @ORM\Column(type="string", length=125)
     *
     * @var string
     */
    private $role;

    /**
     * @ORM\Column(type="array", nullable=true)
     *
     * @var int[]
     */
    private $votes = [];

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'name' => $this->username,
            'role' => $this->role,
            'votes' => $this->votes
        ];
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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array
    {
        $role = $this->getRole();
        if($role == null) {
            throw new \LogicException("could not get role for user ".$this->getUsername());
        } else {
            return [
                $role,
            ];
        }

    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials(): self
    {
        return $this;
    }

    public function serialize(): string
    {
        $serial = serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
        return $serial;
    }

    public function unserialize($string)
    {
        list(
            $this->id,
            $this->username,
            $this->password
        ) = unserialize($string, ['allowed_classes' => false]);
    }

    public function getVotes(): array
    {
        return $this->votes;
    }

    public function addVote(int $survey_id, int $vote_id): self
    {
        if(!isset($this->votes[$survey_id]) || !array_key_exists($survey_id, $this->votes)) {
            $this->votes[$survey_id] = $vote_id;
        } else {
            throw new \LogicException("User has already voted on survey (id: ".$survey_id.")");
        }

        return $this;
    }

    public function removeVote(int $survey_id): self
    {
        unset($this->votes[$survey_id]);

        return $this;
    }

    public function hasVoted(int $survey_id): bool
    {
        $votes = $this->votes;
        if($votes != null) {
            foreach($votes as $key => $value) {
                if($key == $survey_id) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }
}
