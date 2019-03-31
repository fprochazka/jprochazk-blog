<?php


namespace App\DTO;

use App\Entity\User;

class CreatePostDto
{
    /** @var string */
    private $title;

    /** @var string */
    private $content;

    public function __construct
    (
        string $title,
        string $content
    )
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }


}
