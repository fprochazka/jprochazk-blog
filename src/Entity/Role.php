<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SurveyOptionRepository")
 */
class Role
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
     * @ORM\Column(type="string", length=20)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="Roles")
     */
    private $Users;

    public function __construct(string $name)
    {
        assert(strlen($name) <= 20);
        $this->name = $name;
        $this->Users = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Collection|User[]
     */
    public function getPeople(): Collection
    {
        return $this->Users;
    }

    public function addPerson(User $user): self
    {
        if (!$this->Users->contains($user)) {
            $this->Users[] = $user;
            $user->addRole($this);
        }

        return $this;
    }

    public function removePerson(User $user): self
    {
        if ($this->Users->contains($user)) {
            $this->Users->removeElement($user);
            $user->removeRole($this);
        }

        return $this;
    }
}
