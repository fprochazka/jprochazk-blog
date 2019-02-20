<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Exception\LogicException;

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
     * @var SurveyOption[]
     */
    private $Options;

    public function __construct()
    {
        $this->Options = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function toArray(): array 
    {
        $options = [];
        foreach($this->getSortedOptions() as $option) $options[] = $option->toArray();

        return [
            "id" => $this->id,
            "title" => $this->title, 
            "options" => $options
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
     * @return Survey
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Survey
     */
    public function resetOptions(): self
    {
        $this->Options = new ArrayCollection();

        return $this;
    }

    /**
     * @param int $id
     * @return SurveyOption
     */
    public function getOptionById(int $id): SurveyOption
    {
        foreach($this->Options as $option) {
            if($option->getId() == $id) {
                return $option;
            }
        } 
        throw new \LogicException("could not find SurveyOption(id: ".$id.") in Survey(id: ".$this->getId().")");
    }

    /**
     * @return Collection|SurveyOption[]
     */
    public function getOptions(): Collection
    {   
        return $this->Options;
    }

    /**
     * @return Collection
     */
    public function getSortedOptions(): Collection
    {   
        $sorted_options = new ArrayCollection;
        $unsorted_options = $this->Options;
        $temp_ids = [];

        //first retrieve id of each option
        foreach($unsorted_options as $option) {
            $temp_ids[] = $option->getId();
        }

        //sort these ids in ascending order (highest last)
        sort($temp_ids);

        //add each option in by its ID, thereby adding them in ascending order sorted by the ID
        foreach($temp_ids as $id) {
            $sorted_options[] = $this->getOptionById($id);
        }

        return $sorted_options;
    }

    /**
     * @param SurveyOption $option
     * @return Survey
     */
    public function addOption(SurveyOption $option): self
    {
        if (!$this->Options->contains($option)) {
            $this->Options[] = $option;
            $option->setSurvey($this);
        }

        return $this;
    }

    /**
     * @param SurveyOption $option
     * @return Survey
     */
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

    /**
     * @param SurveyOption $option
     * @return Survey
     */
    public function incrementVote(SurveyOption $option): self
    {
        if($this->Options->contains($option)) {
            $option->incrementVote();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @return Survey
     */
    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @return Survey
     */
    public function unlock(): self
    {
        $this->locked = false;

        return $this;
    }
}
