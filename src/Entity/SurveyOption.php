<?php

namespace App\Entity;

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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $votes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey", inversedBy="Options", cascade={"persist"})
     */
    private $Survey;

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->title,
            "votes" => $this->votes,
        ];
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

    public function getVotes(): ?int
    {
        return $this->votes;
    }

    public function setVotes(?int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function incrementVote(): self
    {
        $this->votes += 1;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->Survey;
    }

    public function setSurvey(?Survey $Survey): self
    {
        $this->Survey = $Survey;

        return $this;
    }
}
