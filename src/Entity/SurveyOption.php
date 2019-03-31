<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SurveyOptionRepository")
 */
class SurveyOption
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
     * @ORM\Column(type="string", length=500)
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $votes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey", inversedBy="Options", cascade={"persist"})
     *
     * @var Survey
     */
    private $Survey;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="votes")
     *
     * @var Collection
     */
    private $users;

    public function __construct()
    {
        $this->votes = 0;
        $this->users = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getVotes(): int
    {
        return $this->votes;
    }

    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function incrementVote(): self
    {
        $this->votes += 1;

        return $this;
    }

    public function getSurvey(): Survey
    {
        return $this->Survey;
    }

    public function setSurvey(Survey $Survey): self
    {
        $this->Survey = $Survey;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addVote($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeVote($this);
        }

        return $this;
    }
}
