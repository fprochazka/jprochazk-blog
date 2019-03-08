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

    public function __construct()
    {
        $this->Options = new ArrayCollection();
    }

    public function toArray(): array 
    {
        $options = [];
        foreach($this->getSortedOptions() as $option) $options[] = $option->toArray();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'options' => $options
        ];
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

    public function resetOptions(): self
    {
        $this->Options = new ArrayCollection();

        return $this;
    }

    public function getOptionById(int $id): SurveyOption
    {
        foreach($this->Options as $option) {
            if($option->getId() == $id) {
                return $option;
            }
        } 
        throw new \LogicException('could not find SurveyOption(id: '.$id.') in Survey(id: '.$this->getId().')');
    }

    public function getOptions(): ?Collection
    {   
        return $this->Options;
    }

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
