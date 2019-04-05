<?php

namespace App\Controller;

use App\Facade\SearchFacade;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            if($form->isValid()) {
                $query = $form->getData()['query'];
                if($query !== null) {
                    return $this->redirectToRoute('app_blog_search_result', [
                        's' => $query,
                    ]);
                }
            } else {
                return new RedirectResponse($request->headers->get('referer'));
            }
        }

        return $this->render('blog/search/form.twig', [
            'search_form' => $form->createView()
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
            'query' => $query,
            'results' => $results,
        ]);
    }
}
