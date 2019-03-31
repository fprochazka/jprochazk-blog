<?php


namespace App\DTO;

use Doctrine\Common\Collections\Collection;

class CreateSurveyDto
{
    /** @var string */
    private $title;

    /** @var Collection */
    private $options;

    public function __construct
    (
        string $title,
        Collection $options
    )
    {
        $this->title = $title;
        $this->options = $options;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }
}