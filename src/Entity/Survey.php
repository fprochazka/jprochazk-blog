<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SurveyRepository")
 */
class Survey
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=800)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SurveyOption", mappedBy="Survey")
     */
    private $Options;

    public function __construct()
    {
        $this->Options = new ArrayCollection();
    }

    public function getId(): ?int
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

    /**
     * @return Collection|Surveyoption[]
     */
    public function getOptions(): Collection
    {
        return $this->Options;
    }

    public function getOption($option_id): SurveyOption
    {
        return $this->Options->get($option_id);
    }

    public function addOption(SurveyOption $option): self
    {
        if (!$this->Options->contains($option)) {
            $this->Options[] = $option;
            $option->setSurvey($this);
        }

        return $this;
    }

    public function removeOption(SurveyOption $option): self
    {
        if ($this->Options->contains($option)) {
            $this->Options->removeElement($option);
            // set the owning side to null (unless already changed)
            if ($option->getSurvey() === $this) {
                $option->setSurvey(null);
            }
        }

        return $this;
    }

    public function incrementVote(int $vote_id): self
    {
        $this->getOption($vote_id)->incrementVotesBy(1);

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function lock(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
