<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Post;
use App\Form\SearchFormType;

class SearchController extends AbstractController
{
    /**
     * @Route("/search_form", name="app_blog_search_form")
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
     */
    public function searchResults(Request $request) {

        $str = $request->query->get('s');

        $results = [];

        $results_title = $this->getDoctrine()->getRepository(Post::class)->findByTitle($str);
        $results_content = $this->getDoctrine()->getRepository(Post::class)->findByContent($str);

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
