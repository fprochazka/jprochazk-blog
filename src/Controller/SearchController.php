<?php

namespace App\Controller;

use App\Facade\SearchFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    /** @var SearchFacade */
    private $searchFacade;

	public function __construct(
	    SearchFacade $searchFacade
	)
	{
	    $this->searchFacade = $searchFacade;
	}

	/**
     * @Route("/search_form", name="app_blog_search_form")
     */
    public function sendSearch(Request $request): Response
    {
        $query = $this->searchFacade->getQuery($request);
        if($query !== null) {
            return $this->redirectToRoute('app_blog_search_result', [
                's' => $query,
            ]);
        }

        return $this->render('blog/search/form.twig', [
            'search_form' => $this->searchFacade->getSearchFormView()
        ]);
    }

    /**
     * @Route("/search", name="app_blog_search_result")
     */
    public function searchResults(Request $request): Response
    {
        $query = $request->query->get('s');

        $results = $this->searchFacade->findPosts($query);

        return $this->render('blog/search/results.html.twig', [
            'search_string' => $query,
            'results' => $results,
        ]);
    }
}
