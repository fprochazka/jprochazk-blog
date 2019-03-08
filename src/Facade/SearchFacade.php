<?php


namespace App\Facade;


use App\Entity\Post;
use App\Form\SearchFormType;
use App\Repository\PostRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class SearchFacade
{
    /** @var PostRepository */
    private $postRepository;
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var RouterInterface  */
    private $router;

    public function __construct(
        PostRepository $postRepository,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    )
    {
        $this->postRepository = $postRepository;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    public function getSearchFormView(): FormView
    {
        return $this->formFactory->create(SearchFormType::class)->createView();
    }

    public function getSearchForm(): FormInterface
    {
        return $this->formFactory->create(SearchFormType::class);
    }

    public function getQuery(Request $request): ?string
    {
        $form = $this->getSearchForm();
        $form->handleRequest($request);

        $data = null;

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData()['query'];
        }

        return $data;
    }

    private function formatResults(string $query, array $results): array
    {
        $formattedResults = [];

        /** @var Post $result */
        foreach($results['byTitle'] as $result) {
            $id = $result->getId();
            if(!isset($formattedResults[$id]) || !array_key_exists($id, $formattedResults)) {
                $formattedResults[$id] = $result->toArray();
            }
        }

        /** @var Post $result */
        foreach($results['byContent'] as $result) {
            $id = $result->getId();
            if(!isset($formattedResults[$id]) || !array_key_exists($id, $formattedResults)) {
                $formattedResults[$id] = $result->toArray();
            }
        }

        return $formattedResults;
    }

    public function findPosts(string $query): array
    {
        $results = [];
        $results['byTitle'] = $this->postRepository->findByTitle($query);
        $results['byContent'] = $this->postRepository->findByContent($query);

        $response = $this->formatResults($query, $results);

        return $response;
    }
}