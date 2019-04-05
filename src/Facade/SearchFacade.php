<?php


namespace App\Facade;


use App\Entity\Post;
use App\Repository\PostRepository;

class SearchFacade
{
    /** @var PostRepository */
    private $postRepository;

    public function __construct(
        PostRepository $postRepository
    )
    {
        $this->postRepository = $postRepository;
    }

    public function findPosts(string $query, int $start = 0, int $count = 10): array
    {
        $results['byTitle'] = $this->postRepository->findByTitle($query, $start, $count);
        $results['byContent'] = $this->postRepository->findByContent($query, $start, $count);

        $formattedResults = [];

        /** @var Post $result */
        foreach($results['byTitle'] as $result) {
            $id = $result->getId();
            if(!isset($formattedResults[$id]) || !array_key_exists($id, $formattedResults)) {
                $formattedResults[$id] = $result;
            }
        }

        foreach($results['byContent'] as $result) {
            $id = $result->getId();
            if(!isset($formattedResults[$id]) || !array_key_exists($id, $formattedResults)) {
                $formattedResults[$id] = $result;
            }
        }

        return $formattedResults;
    }
}	