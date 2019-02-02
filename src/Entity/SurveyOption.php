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
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey", inversedBy="Options")
     */
    private $Survey;

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

    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function incrementVotesBy(int $num): self
    {
        $this->votes += $num;

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
