<?php


namespace App\DTO;


class EditCommentDto
{
    private $content;
    private $comment_id;
    private $author_username;

    public function __construct
    (
        string $content,
        int $comment_id,
        string $author_username
    )
    {
        $this->content = $content;
        $this->comment_id = $comment_id;
        $this->author_username = $author_username;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCommentId(): int
    {
        return $this->comment_id;
    }

    public function getAuthorUsername(): string
    {
        return $this->author_username;
    }
}