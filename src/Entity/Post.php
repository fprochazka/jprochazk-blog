<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
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
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=8000)
     *
     * @var string
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTimeInterface
     */
    private $subtime;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @var Collection
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function toArray(): array
    {
        $comments = [];
        foreach($this->comments as $comment) $comments[] = $comment->toArray();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'date' => $this->subtime->format('H:i:s, Y-m-d'),
            'author' => $this->author,
            'comments' => $comments
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSubtime(): \DateTimeInterface
    {
        return $this->subtime;
    }

    public function setSubtime(\DateTimeInterface $subtime): self
    {
        $this->subtime = $subtime;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->getComments()->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->getComments()->contains($comment)) {
            $this->comments->removeElement($comment);
        }

        return $this;
    }
}
