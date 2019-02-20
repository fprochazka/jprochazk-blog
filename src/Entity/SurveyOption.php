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
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->title,
            "votes" => $this->votes,
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
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return SurveyOption
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVotes(): ?int
    {
        return $this->votes;
    }

    /**
     * @param int|null $votes
     * @return SurveyOption
     */
    public function setVotes(?int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * @return SurveyOption
     */
    public function incrementVote(): self
    {
        $this->votes += 1;

        return $this;
    }

    /**
     * @return Survey|null
     */
    public function getSurvey(): ?Survey
    {
        return $this->Survey;
    }

    /**
     * @param Survey|null $Survey
     * @return SurveyOption
     */
    public function setSurvey(?Survey $Survey): self
    {
        $this->Survey = $Survey;

        return $this;
    }
}
