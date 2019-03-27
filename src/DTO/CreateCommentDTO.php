<?php


namespace App\DTO;

class CreateCommentDto
{
    private $post_id;
    private $content;

    public function __construct
    (
        string $content,
        int $post_id
    )
    {
        $this->post_id = $post_id;
        $this->content = $content;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}