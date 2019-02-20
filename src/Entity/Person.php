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
     * @var ArrayCollection|array
     */
    private $votes = [];
    //stores a set of survey ids that the user has voted on

    /**
     * @return array
     */
    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'name' => $this->username,
            'role' => $this->role,
            'votes' => $this->votes
        ];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Person
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Person
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return Person
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return [
            $this->getRole(),
        ];
    }

    /**
     * @return null
     */
    public function getSalt() {
        return null;
    }

    /**
     * @return null
     */
    public function eraseCredentials() {
        return null;
    }

    /**
     * @return string
     */
    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    /**
     * @param string $string
     */
    public function unserialize($string) {
        list(
            $this->id,
            $this->username,
            $this->password
        ) = unserialize($string, ['allowed_classes' => false]);
    }

    /**
     * @return array|null
     */
    public function getVotes(): ?array
    {
        return $this->votes;
    }

    /**
     * @return Person|null
     */
    public function setVotes(): ?self
    {
        return $this;
    }

    /**
     * @param int $survey_id
     * @param int $vote_id
     * @return Person
     */
    public function addVote(int $survey_id, int $vote_id): self
    {
        if(!isset($this->votes[$survey_id]) || !array_key_exists($survey_id, $this->votes)) {
            $this->votes[$survey_id] = $vote_id;
        } else {
            throw new \LogicException("User has already voted on survey (id: ".$survey_id.")");
        }

        return $this;
    }

    /**
     * @param int $survey_id
     * @return Person
     */
    public function removeVote(int $survey_id): self
    {
        unset($this->votes[$survey_id]);

        return $this;
    }

    /**
     * @param int $survey_id
     * @return bool|null
     */
    public function hasVoted(int $survey_id): ?bool
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
