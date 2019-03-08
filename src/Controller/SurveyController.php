<?php

namespace App\Controller;

use App\Facade\AuthenticationFacade;
use App\Facade\SurveyFacade;
use App\Repository\PersonRepository;
use App\Repository\SurveyOptionRepository;
use App\Repository\SurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Survey;
use App\Form\SurveyType;

class SurveyController extends AbstractController
{
    /** @var AuthenticationFacade */
    private $authFacade;

    /** @var SurveyFacade */
    private $surveyFacade;

	/** @var PersonRepository */
	private $personRepository;

	/** @var SurveyRepository */
	private $surveyRepository;

	/** @var SurveyOptionRepository */
	private $surveyOptionRepository;

	public function __construct(
	    AuthenticationFacade $authFacade,
        SurveyFacade $surveyFacade,
		PersonRepository $personRepository,
		SurveyRepository $surveyRepository,
		SurveyOptionRepository $surveyOptionRepository
	)
	{
	    $this->authFacade = $authFacade;
        $this->surveyFacade = $surveyFacade;
		$this->personRepository = $personRepository;
		$this->surveyRepository = $surveyRepository;
		$this->surveyOptionRepository = $surveyOptionRepository;
	}

    /**
     * @Route("/survey", name="app_blog_survey")
     */
    public function renderSurvey(): Response
    {
        return $this->render(
            'blog/survey/render.html.twig', [
            'survey' => $this->surveyFacade->getLatestSurvey()
        ]);
    }

    /**
      * @Route("/survey/new", name="app_blog_survey_new")
      */
    public function createSurvey(Request $request): Response
    {
        if(($authenticationError = $this->authFacade->getAuthenticationError()) !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $response = $this->surveyFacade->createSurvey($request);

        if($response['status'] === 200) {
            return $this->redirectToRoute('app_blog_post_list');
        } else {
            return $this->render('blog/survey/new.html.twig', [
                'form' => $this->surveyFacade->getSurveyFormView(),
            ]);
        }
    }

    /**
      * @Route("/survey/delete/{id<\d+>}", name="app_blog_survey_delete")
      */
    public function deleteSurvey(int $id, Request $request): Response
    {
        if(($authenticationError = $this->authFacade->getAuthenticationError()) !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $response = $this->surveyFacade->deleteSurvey($id);
        if($response['status'] === 200) {
            return new RedirectResponse($request->headers->get('referer'));
        } else {
            return $this->redirectToRoute('app_blog_error', ['msg' => '500']);
        }

    }

    /**
      * @Route("/survey/vote", name="app_blog_survey_vote")
      */
    public function surveyVote(Request $request): JsonResponse
    {
        $response = $this->surveyFacade->surveyVote($request);

        return new JsonResponse([
        	'status' => $response['status'],
        	'message' => $response['message'],
	        200]);
    }
}
