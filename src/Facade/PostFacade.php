<?php


namespace App\Facade;

use App\DTO\CreatePostDto;
use App\DTO\DeletePostDto;
use App\DTO\EditPostDto;
use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Security\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class PostFacade
{
    /** @var CurrentUserProvider  */
    private $userProvider;

    /** @var PostRepository */
    private $postRepository;

    /** @var EntityManagerInterface  */
    private $entityManager;

    public function __construct(
        CurrentUserProvider $userProvider,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->postRepository = $postRepository;
        $this->userProvider = $userProvider;
        $this->entityManager = $entityManager;
    }

    public function getFrontPagePosts(int $start = 0): ?array
    {
        return $this->postRepository->findAllByOffsetCount($start, 10);
    }

    public function getPostById(int $id): ?Post
    {
        return $this->postRepository->find($id);
    }

    public function createPost(CreatePostDto $dto): Post
    {
        $post = new Post();

        $post
            ->setTitle($dto->getTitle())
            ->setContent($dto->getContent())
            ->setAuthor($this->userProvider->getUser());

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function updatePost(EditPostDto $dto): Post
    {
        $post = $this->postRepository->find($dto->getId());

        $post
            ->setTitle($dto->getTitle())
            ->setContent($dto->getContent());

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function deletePost(DeletePostDto $dto): void
    {
        $post = $this->postRepository->find($dto->getId());

        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

}