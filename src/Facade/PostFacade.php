<?php


namespace App\Facade;

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

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityManagerInterface  */
    private $entityManager;

    public function __construct(
        CurrentUserProvider $userProvider,
        PostRepository $postRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->postRepository = $postRepository;
        $this->userProvider = $userProvider;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    public function getFrontPagePosts(int $start = 0): ?array
    {
        return $this->postRepository->findAllByOffsetCount($start, 10);
    }

    public function getSinglePost(int $id): ?Post
    {
        $post = $this->postRepository->find($id);
        return $post;
    }

    public function getPostFormView(?Post $post = null): FormView
    {
        if($post === null) $post = new Post();
        return $this->formFactory->create(PostFormType::class, $post)->createView();
    }

    private function savePost(Post $post): bool
    {
        try {
            $this->entityManager->persist($post);
            $this->entityManager->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }

    }

    public function createPost(Request $request): array
    {
        $returnArray = [
            'status' => 500,
            'error' => null
        ];
        $user = $this->userProvider->getUser();

        if($user === null) {
            $returnArray['error'] = 'Unauthenticated';
            return $returnArray;
        }

        $form = $this->formFactory->create(PostFormType::class, new Post());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) {
                $post = $form->getData();
                $post->setSubtime(new \DateTimeImmutable());
                $post->setAuthor($user);

                if($this->savePost($post)) {
                    $returnArray['status'] = 200;
                    $returnArray['post_id'] = $post->getId();
                } else {
                    $returnArray['error'] = 'Post could not be saved';
                }
            } else {
                $returnArray['error'] = 'Form is invalid';
            }
        }

        return $returnArray;
    }

    public function editPost(int $id, Request $request): array
    {
        $returnArray = [
            'status' => 500,
            'error' => null
        ];

        $post = $this->postRepository->find($id);
        if($post !== null) {
            $form = $this->formFactory->create(PostFormType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $post = $form->getData();
                    $post->setSubtime(new \DateTimeImmutable());

                    if ($this->savePost($post)) {
                        $returnArray['status'] = 200;
                        $returnArray['post_id'] = $post->getId();
                    } else {
                        $returnArray['error'] = 'Post could not be saved';
                    }
                } else {
                    $returnArray['error'] = 'Form is invalid';
                }
            } else {
                $returnArray['post_data'] = $post;
            }
        } else {
            $returnArray['status'] = 404;
        }

        return $returnArray;
    }

    private function removePost(int $id): bool
    {
        $post = $this->postRepository->find($id);
        if($post !== null) {
            try {
                foreach ($post->getComments() as $comment) {
                    $post->removeComment($comment);
                    $this->entityManager->remove($comment);
                }

                $this->entityManager->remove($post);
                $this->entityManager->flush();
                return true;
            } catch (ORMException $e) {
                return false;
            }
        }
        return false;
    }

    public function deletePost(int $id): array
    {

        if($this->removePost($id)) {
            return [
                'status' => 200,
                'error' => null
            ];
        } else {
            return [
                'status' => 404,
                'error' => 'Could not find post'
            ];
        }
    }

}