<?php


namespace App\Facade;


use App\Repository\PostRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class AdminFacade
{
    /** @var UserRepository */
    private $userRepository;

    /** @var PostRepository */
    private $postRepository;

    /** @var SurveyRepository */
    private $surveyRepository;

    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository,
        SurveyRepository $surveyRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->surveyRepository = $surveyRepository;
    }

    public function getAdminUserData(): array
    {
        return [
            'users' => $this->userRepository->findAll()
        ];
    }

    public function getAdminPostData(): array
    {
        return [
            'posts' => $this->postRepository->findAll()
        ];
    }

    public function getAdminSurveyData(): array
    {
        return [
            'surveys' => $this->surveyRepository->findAll()
        ];
    }
}
