<?php


namespace App\Facade;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostFacade
{
    /** @var UserInterface|null */
    private $user;

    /** @var PostRepository */
    private $postRepository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var RouterInterface  */
    private $router;

    public function __construct(
        Security $security,
        PostRepository $postRepository,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        EntityManagerInterface $entityManager
    )
    {
        $this->postRepository = $postRepository;
        $this->user = $security->getUser();
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function getFrontPagePosts(int $start = 0): ?array
    {
        /** @var Post[] $posts */
        $unformatted_posts = $this->postRepository->findAllByOffsetCount($start, 10);

        if($unformatted_posts != null) {
            $posts = [];
            $c = 0;
            foreach ($unformatted_posts as $post) {
                /** @var Post $post */
                $posts[$c] = $post->toArray();
                if (strlen($posts[$c]["content"]) > 100) {
                    $posts[$c]["content"] = substr($posts[$c]["content"], 0, 100) . '...';
                }
                ++$c;
            }

            return $posts;
        }

        return null;
    }

    public function getSinglePost(int $id): ?array
    {
        if($post = $this->postRepository->find($id)->toArray()) {
            $current_user_username = ($this->user !== null) ? $this->user->getUsername() : "guest";
            foreach($post["comments"] as $key => $value) {
                $post["comments"][$key]["canEdit"] = ($current_user_username == $post["comments"][$key]["author"]) ? true : false;
            }

            return $post;
        } else {
            return null;
        }
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

        if($this->user === null) {
            $returnArray['error'] = 'Unauthenticated';
            return $returnArray;
        }

        $form = $this->formFactory->create(PostFormType::class, new Post());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) {
                $post = $form->getData();
                $post->setSubtime(new \DateTimeImmutable());
                $post->setAuthor($this->user->getUsername());

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