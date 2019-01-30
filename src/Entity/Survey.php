<?php

namespace App\Entity;

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
     * @ORM\Column(type="array")
     */
    private $options = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked;

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

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function incrementVote(int $vote_id): self
    {
        $this->options[$vote_id]["votes"] += 1;

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
