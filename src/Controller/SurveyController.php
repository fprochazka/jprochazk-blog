<?php

namespace App\Controller;

use App\DTO\CreateSurveyDto;
use App\DTO\SurveyVoteDto;
use App\Exception\SurveyNotFoundException;
use App\Facade\AuthenticationFacade;
use App\Facade\SurveyFacade;
use App\ResponseFactory\SurveyVoteResponseFactory;
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

    /** @var SurveyVoteResponseFactory */
    private $responseFactory;

	public function __construct(
	    AuthenticationFacade $authFacade,
        SurveyFacade $surveyFacade,
        SurveyVoteResponseFactory $responseFactory
	)
	{
	    $this->authFacade = $authFacade;
        $this->surveyFacade = $surveyFacade;
        $this->responseFactory = $responseFactory;
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
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        $form = $this->createForm(SurveyType::class, new Survey());

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $formData = $form->getData();
                $title = $formData->getTitle();
                $options = $formData->getOptions();

                $this->surveyFacade->createSurvey(
                    new CreateSurveyDto(
                        $title,
                        $options
                    )
                );

                return $this->redirectToRoute('app_blog_post_list');
            }
        }

        return $this->render('blog/survey/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
      * @Route("/survey/delete/{id<\d+>}", name="app_blog_survey_delete")
      */
    public function deleteSurvey(int $id, Request $request): Response
    {
        $authenticationError = $this->authFacade->getAuthenticationError();
        if($authenticationError !== null) {
            return $this->redirectToRoute('app_blog_error', ['msg' => $authenticationError]);
        }

        try {
            $this->surveyFacade->deleteSurvey($id);
        } catch(SurveyNotFoundException $e) {
            //TODO: implement flash messages
            //TODO: use flash messages to notify the user that the deletion has failed
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    /**
      * @Route("/survey/vote", name="app_blog_survey_vote")
      */
    public function surveyVote(Request $request): JsonResponse
    {
        $survey_id = (int)$request->request->get('survey_id');
        $option_id = (int)$request->request->get('vote_id');

        $this->surveyFacade->surveyVote(new SurveyVoteDto($survey_id, $option_id));

        return new JsonResponse(
            $this->responseFactory->getSurveyVoteJson($option_id)
        );
    }
}
