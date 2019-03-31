<?php


namespace App\DTO;


class DeleteCommentDto
{
    /** @var int $comment_id */
    private $comment_id;

    /** @var int $post_id */
    private $post_id;

    /** @var string $author_username */
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
