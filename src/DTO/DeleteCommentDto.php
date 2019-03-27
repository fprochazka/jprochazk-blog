<?php


namespace App\DTO;


class DeleteCommentDto
{
    private $comment_id;
    private $post_id;
    private $author_username;

    public function __construct
    (
        int $comment_id,
        int $post_id,
        string $author_username
    )
    {
        $this->comment_id = $comment_id;
        $this->post_id = $post_id;
        $this->author_username = $author_username;
    }

    public function getCommentId(): string
    {
        return $this->comment_id;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function getAuthorUsername(): string
    {
        return $this->author_username;
    }
}