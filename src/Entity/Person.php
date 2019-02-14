<?php

namespace App\Entity;

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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $role;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $votes = [];
    //stores a set of survey ids that the user has voted on

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'name' => $this->username,
            'role' => $this->role,
            'votes' => $votes
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles()
    {
        return [
            $this->getRole(),
        ];
    }

    public function getSalt() {}

    public function eraseCredentials() {}
    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($string) {
        list(
            $this->id,
            $this->username,
            $this->password
        ) = unserialize($string, ['allowed_classes' => false]);
    }

    public function getVotes(): ?array
    {
        return $this->votes;
    }

    // do not use- requires an entire array of vote ids to be submitted
    public function setVotes(?array $vote): self
    {
        $this->votes = $vote;

        return $this;
    }

    public function addVote(?int $survey_id, ?int $vote_id): self
    {
        if(!isset($this->votes[$survey_id]) || !array_key_exists($survey_id, $this->votes)) {
            $this->votes[$survey_id] = $vote_id;
        } else {
            throw new \LogicException("User has already voted on survey (id: ".$survey_id.")");
        }

        return $this;
    }

    public function removeVote(?int $survey_id): self
    {
        unset($this->votes[$survey_id]);

        return $this;
    }

    public function hasVoted(?int $survey_id): ?bool
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
