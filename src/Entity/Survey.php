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
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=800)
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $locked;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SurveyOption", mappedBy="Survey")
     * @ORM\OrderBy=({"id" = "ASC"})
     *
     * @var Collection
     */
    private $Options;

    public function __construct(string $title = "")
    {
        $this->title = $title;
        $this->unlock();
        $this->Options = new ArrayCollection();
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

    public function getOptions(): ?Collection
    {   
        return $this->Options;
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
        }

        return $this;
    }

    public function incrementVote(SurveyOption $option): self
    {
        if($this->Options->contains($option)) {
            $option->incrementVote();
        }

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    public function unlock(): self
    {
        $this->locked = false;

        return $this;
    }
}
