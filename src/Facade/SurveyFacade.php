<?php


namespace App\Facade;

use App\Entity\Person;
use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\PersonRepository;
use App\Repository\SurveyOptionRepository;
use App\Repository\SurveyRepository;
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
    /** @var UserInterface|null  */
    private $user;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var PersonRepository */
    private $personRepository;

    /** @var SurveyRepository */
    private $surveyRepository;

    /** @var SurveyOptionRepository */
    private $surveyOptionRepository;

    public function __construct(
        Security $security,
        PersonRepository $personRepository,
        SurveyRepository $surveyRepository,
        SurveyOptionRepository $surveyOptionRepository,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    )
    {
        $this->user = $security->getUser();
        $this->personRepository = $personRepository;
        $this->surveyRepository = $surveyRepository;
        $this->surveyOptionRepository = $surveyOptionRepository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function getLatestSurvey(): ?array
    {
        $survey = $this->surveyRepository->findOneByHighestId();
        if($survey !== null) {
            $survey = $survey->toArray();
            /** @var Person $user */
            $user = $this->user;
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
            $users = $this->personRepository->findAll();
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
}