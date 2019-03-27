<?php


namespace App\Facade;

use App\Entity\User;
use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\SurveyOptionRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SurveyFacade
{
    /** @var CurrentUserProvider */
    private $userProvider;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserRepository */
    private $userRepository;

    /** @var SurveyRepository */
    private $surveyRepository;

    /** @var SurveyOptionRepository */
    private $surveyOptionRepository;

    public function __construct(
        CurrentUserProvider $userProvider,
        UserRepository $userRepository,
        SurveyRepository $surveyRepository,
        SurveyOptionRepository $surveyOptionRepository,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    )
    {
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
        $this->surveyRepository = $surveyRepository;
        $this->surveyOptionRepository = $surveyOptionRepository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function getLatestSurvey(): ?Survey
    {
        $survey = $this->surveyRepository->findOneByHighestId();
        if($survey !== null) {
            /** @var User $user */
            $user = $this->userProvider->getUser();
            if($user != null) {
                $survey['voted'] = $user->hasVoted($survey['id']);
            } else {
                $survey['voted'] = true;
            }

            return $survey;
        } else {
            return null;
        }
    }

    private function getSurveyForm(?Survey $options = null): FormInterface
    {
        if($options === null) $survey = new Survey();
        else $survey = $options;
        return $this->formFactory->create(SurveyType::class, $survey);
    }

    public function getSurveyFormView(?Survey $options = null): FormView
    {
        return $this->getSurveyForm($options)->createView();
    }

    private function saveSurvey(Survey $data): bool
    {
        try {
            $survey = new Survey();
            $title = $data->getTitle();
            if($title !== null) {
                $survey->setTitle($title);
            } else {
                throw new ORMException();
            }

            $survey->unlock();

            $options = $data->getOptions();
            if($options !== null) {
                foreach ($options as $option) {
                    $survey->addOption($option);
                    $option->setVotes(0);
                    $this->entityManager->persist($option);
                }
            } else {
                throw new ORMException();
            }


            $this->entityManager->persist($survey);
            $this->entityManager->flush();
            return true;
        } catch(ORMException $e) {
            return false;
        }
    }

    public function createSurvey(Request $request): array
    {
        $response = [
            'status' => 0
        ];

        $survey = new Survey();
        $form = $this->formFactory->create(SurveyType::class, $survey);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                if($this->saveSurvey($data)) {
                    $response['status'] = 200;
                } else {
                    $response['status'] = 500;
                    $response['message'] = 'Could not save survey';
                    $response['data'] = $form->getData();
                }
            } else {
                $response['status'] = 500;
                $response['message'] = 'Form is invalid';
                $response['data'] = $form->getData();
            }
        }

        return $response;
    }

    public function deleteSurvey(int $id): array
    {
        $response = [
            'status' => 0
        ];

        if($survey = $this->surveyRepository->find($id)) {
            $users = $this->userRepository->findAll();
            foreach($users as $user) {
                $user->removeVote($id);
            }

            try {
                foreach($survey->getOptions() as $option) {
                    $survey->removeOption($option);
                    $this->entityManager->remove($option);
                }
                $this->entityManager->remove($survey);
                $this->entityManager->flush();

                $response['status'] = 200;
            } catch(ORMException $e) {
                $response['status'] = 500;
                $response['message'] = 'Could not delete survey('.$id.') from database';
            }
        } else {
            $response['status'] = 500;
            $response['message'] = 'Could not find survey('.$id.')';
        }

        return $response;
    }

    public function surveyVote(Request $request): array
    {
        $response = [
            'status' => 400,
            'message' => 'Error'
        ];

        if($request->isXmlHttpRequest() || $request->query->get('showJson') === 1) {

            $survey_id = (int)$request->request->get('survey_id');
            $vote_id = (int)$request->request->get('vote_id');

            $survey = $this->surveyRepository->find($survey_id);
            $option = $this->surveyOptionRepository->find($vote_id);

            /** @var User $current_user */
            $current_user = $this->user;
            if(!$survey->isLocked()) {
                if(!$current_user->hasVoted($survey_id)){

                    $option->incrementVote();
                    $current_user->addVote($survey, $option);
                    $this->entityManager->flush();

                    $response = [
                        'status' => 200,
                        'message' => [
                            'vote_id' => $vote_id
                        ]
                    ];
                } else {
                    $response = [
                        'status' => 400,
                        'message' => 'User has already voted'
                    ];
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Survey is locked'
                ];
            }
        }

        return $response;
    }
}