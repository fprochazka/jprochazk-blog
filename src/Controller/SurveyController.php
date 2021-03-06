<?php

namespace App\Controller;

use App\DTO\CreateSurveyDto;
use App\DTO\SurveyVoteDto;
use App\Exception\SurveyNotFoundException;
use App\Facade\AuthenticationFacade;
use App\Facade\SurveyFacade;
use App\Form\SurveyDeleteType;
use App\ResponseFactory\SurveyVoteResponseFactory;
use Psr\Log\LoggerInterface;
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

    /** @var LoggerInterface */
    private $logger;

	public function __construct(
	    AuthenticationFacade $authFacade,
        SurveyFacade $surveyFacade,
        SurveyVoteResponseFactory $responseFactory,
        LoggerInterface $logger
	)
	{
	    $this->authFacade = $authFacade;
        $this->surveyFacade = $surveyFacade;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
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

                try {
                    $this->surveyFacade->createSurvey(
                        new CreateSurveyDto(
                            $title,
                            $options
                        )
                    );

                    $this->addFlash('notice', 'Survey successfully created');
                    return $this->redirectToRoute('app_blog_post_list');
                } catch(\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->addFlash('notice', 'Failed to save survey');
                }
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

        $form = $this->createForm(SurveyDeleteType::class);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                try {
                    $this->surveyFacade->deleteSurvey($id);
                    $this->addFlash('notice', 'Survey successfully deleted');
                    return new RedirectResponse($request->headers->get('referer'));
                } catch(SurveyNotFoundException $e) {
                    $this->addFlash('notice', 'Survey does not exist');
                    return new RedirectResponse($request->headers->get('referer'));
                } catch(\Exception $e) {
                    $this->addFlash('notice', 'Error while deleting survey');
                    $this->logger->error($e->getMessage());
                    return new RedirectResponse($request->headers->get('referer'));
                }
            } else {
                $error_string = "";
                foreach($form->getErrors() as $error) {
                    $error_string .= $error->getMessage();
                }
                $this->logger->error($error_string);
                $this->addFlash('notice', 'Failed to delete post');
                return new RedirectResponse($request->headers->get('referer'));
            }
        }

        return $this->render('blog/survey/delete.button.html.twig', [
            'survey_delete_form' => $form->createView(),
            'survey_id' => $id
        ]);
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
