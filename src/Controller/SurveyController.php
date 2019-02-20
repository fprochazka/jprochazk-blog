<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use App\Repository\SurveyOptionRepository;
use App\Repository\SurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Person;
use App\Entity\Survey;
use App\Entity\SurveyOption;
use App\Form\SurveyType;

class SurveyController extends AbstractController
{

	/** @var PersonRepository */
	private $personRepository;

	/** @var SurveyRepository */
	private $surveyRepository;

	/** @var SurveyOptionRepository */
	private $surveyOptionRepository;

	public function __construct(
		PersonRepository $personRepository,
		SurveyRepository $surveyRepository,
		SurveyOptionRepository $surveyOptionRepository
	)
	{
		$this->personRepository = $personRepository;
		$this->surveyRepository = $surveyRepository;
		$this->surveyOptionRepository = $surveyOptionRepository;
	}

    /**
      * @Route("/survey/new", name="app_blog_survey_new")
     *
     * @param Request $request
     * @return Response|RedirectResponse
      */
    public function createSurvey(Request $request) {
        $_error = "";
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                $survey = new Survey();
                $form = $this->createForm(SurveyType::class, $survey);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $data = $form->getData();

                    $survey->setTitle($data->getTitle());
                    $survey->unlock();

                    foreach($data->getOptions() as $option) {
                        $survey->addOption($option);
                        $option->setVotes(0);
                        $entityManager->persist($option);
                    }

                    $entityManager->persist($survey);
                    $entityManager->flush();

                    return $this->redirectToRoute("app_blog_post_list");
                }

                return $this->render('blog/survey/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/survey/delete/{id<\d+>}", name="app_blog_survey_delete")
     *
     * @param int $id
     * @param Request $request
     * @return Response|RedirectResponse
      */
    public function deleteSurvey($id, Request $request) {
        $_error = "";
        if($current_user = $this->getUser()) {
            if($current_user->getRole() == 'ROLE_ADMIN') {
                if($survey = $this->surveyRepository->find($id)) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $users = $this->personRepository->findAll();
                    foreach($users as $user) {
                        $user->removeVote($id);
                    }

                    foreach($survey->getOptions() as $option) {
                        $survey->removeOption($option);
                        $entityManager->remove($option);
                    }

                    $entityManager->remove($survey);
                    $entityManager->flush();

                    return new RedirectResponse($request->headers->get('referer'));
                } else { $_error = "404"; }
            } else { $_error = "perm"; }
        } else { $_error = "auth"; }

        return $this->redirectToRoute("app_blog_error", ['msg' => $_error]);
    }

    /**
      * @Route("/survey", name="app_blog_survey")
     *
     * @return Response
      */
    public function renderSurvey() {
        if($survey = $this->surveyRepository->findOneByHighestId()) {
            $survey = $survey->toArray();
            if($current_user = $this->getUser()) {
                $survey["voted"] = $current_user->hasVoted($survey["id"]);
            } else {
                $survey["voted"] = true;
            }

            return $this->render(
            'blog/survey/render.html.twig', [
                "survey" => $survey
            ]);

        } else {
            return new Response("");
        }
    }

    /**
      * @Route("/survey/vote", name="app_blog_survey_vote")
     *
     * @param Request $request
     * @return JsonResponse
      */
    public function surveyVote(Request $request) {
        if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {

            $survey_id = (int)$request->request->get('survey_id');
            $vote_id = (int)$request->request->get('vote_id');

            $survey = $this->surveyRepository->find($survey_id);
            $current_user = $this->getUser();
            if(!$survey->isLocked()) {
                if(!$current_user->hasVoted($survey_id)){

                    //this is clunky, but because of the way votes are stored in the Person entity,
                    //it has to be like this
                    $this->surveyOptionRepository->find($vote_id)->incrementVote();
                    $current_user->addVote($survey_id, $vote_id);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();

                    $responseData = [
                        'vote_id' => $vote_id
                    ];

                    return new JsonResponse([
                    'status' => 'OK',
                    'message' => $responseData],
                    200);
                }
            }
        }
        return new JsonResponse([
        	'status' => 'Error',
        	'message' => 'Error'],
	        400
	    );
    }
}
