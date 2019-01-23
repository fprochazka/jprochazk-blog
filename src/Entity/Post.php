<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=8000)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $subtime;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSubtime(): ?\DateTimeInterface
    {
        return $this->subtime;
    }

    public function setSubtime(\DateTimeInterface $subtime): self
    {
        $this->subtime = $subtime;

        return $this;
    }
}
