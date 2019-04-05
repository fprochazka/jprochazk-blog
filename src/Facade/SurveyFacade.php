<?php


namespace App\Facade;

use App\DTO\CreateSurveyDto;
use App\DTO\SurveyVoteDto;
use App\Entity\SurveyOption;
use App\Entity\User;
use App\Entity\Survey;
use App\Exception\SurveyNotFoundException;
use App\Form\SurveyType;
use App\Repository\SurveyOptionRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;

class SurveyFacade
{
    /** @var CurrentUserProvider */
    private $userProvider;

    /** @var EntityManagerInterface */
    private $entityManager;

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
        EntityManagerInterface $entityManager
    )
    {
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
        $this->surveyRepository = $surveyRepository;
        $this->surveyOptionRepository = $surveyOptionRepository;
        $this->entityManager = $entityManager;
    }

    public function getLatestSurvey(): ?Survey
    {
        $survey = $this->surveyRepository->findOneByHighestId();
        return $survey;
    }

    public function createSurvey(CreateSurveyDto $dto): void
    {
        $survey = new Survey();

        $title = $dto->getTitle();
        $options = $dto->getOptions();

        $survey->setTitle($title);

        foreach ($options as $option) {
            $survey->addOption($option);
            $this->entityManager->persist($option);
        }

        $this->entityManager->persist($survey);
        $this->entityManager->flush();
    }

    /**
     * @throws SurveyNotFoundException
     */
    public function deleteSurvey(int $id): void
    {
        if($survey = $this->surveyRepository->find($id)) {
            /** @var SurveyOption $option */
            foreach($survey->getOptions() as $option) {
                $users = $option->getUsers();
                /** @var User $user */
                foreach($users as $user) {
                    $user->removeVote($option);
                }
                $survey->removeOption($option);
                $this->entityManager->remove($option);
            }

            $this->entityManager->remove($survey);
            $this->entityManager->flush();
        } else {
            throw new SurveyNotFoundException();
        }
    }

    public function surveyVote(SurveyVoteDto $dto): void
    {
        $survey = $this->surveyRepository->find($dto->getSurveyId());
        $option = $this->surveyOptionRepository->find($dto->getOptionId());
        $user = $this->userProvider->getUser();

        if(!$survey->isLocked()) {
            if(!$user->hasVoted($dto->getSurveyId())){

                $option->incrementVote();
                $user->addVote($option);
                $this->entityManager->flush();
            }
        }
    }
}
