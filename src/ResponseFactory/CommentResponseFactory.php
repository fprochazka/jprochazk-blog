<?php


namespace App\ResponseFactory;


use App\Entity\Comment;

class CommentResponseFactory
{

    public function getCommentJson(Comment $comment): array
    {
        return [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'author' => $comment->getAuthor()->getUsername(),
            'date' => $comment->getDate()->format("F d, Y H:i")
        ];
    }
}
