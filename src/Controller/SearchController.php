<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Post;
use App\Form\SearchFormType;

class SearchController extends AbstractController
{

	/** @var PostRepository */
	private $postRepository;

	public function __construct(
		PostRepository $postRepository
	)
	{
		$this->postRepository = $postRepository;
	}

	/**
     * @Route("/search_form", name="app_blog_search_form")
     *
     * @param Request $request
     * @return Response
     */
    public function searchForm(Request $request) {
        $form = $this->createForm(SearchFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            return $this->redirectToRoute('app_blog_search_result', [
                's' => $data['query'],
            ]);
        } else {
            return $this->render('blog/search/form.twig', [
                'search_form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/search", name="app_blog_search_result")
     *
     * @param Request $request
     * @return Response
     */
    public function searchResults(Request $request) {

        $str = $request->query->get('s');

        $results = [];

        $results_title = $this->postRepository->findByTitle($str);
        $results_content = $this->postRepository->findByContent($str);

        foreach($results_title as $result) {
            $id = $result->getId();
            if(!isset($results[$id]) || !array_key_exists($id, $results)) {
                $results[$id] = $result->toArray();
                $results[$id]["title"] = str_replace($str, '<span class="highlight">'.$str.'</span>', $result->getTitle());
                $results[$id]["content"] = str_replace($str, '<span class="highlight">'.$str.'</span>', $result->getContent());
            }
        }

        foreach($results_content as $result) {
            $id = $result->getId();
            if(!isset($results[$id]) || !array_key_exists($id, $results)) {
                $results[$id] = $result->toArray();
                $results[$id]["title"] = str_replace($str, '<span class="highlight">'.$str.'</span>', $result->getTitle());
                $results[$id]["content"] = str_replace($str, '<span class="highlight">'.$str.'</span>', $result->getContent());
            }
        }

        return $this->render('blog/search/results.html.twig', [
            'search_string' => $str,
            'results' => $results,
        ]);
    }
}
