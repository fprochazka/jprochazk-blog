<?php


namespace App\Facade;


use App\Repository\PostRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class AdminFacade
{
    /** @var $userRepository  */
    private $userRepository;
    /** @var PostRepository  */
    private $postRepository;
    /** @var SurveyRepository  */
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

    private function getAdminData(?string $tab): ?array
    {
        $data = null;
        switch($tab) {
            case 'users':
                foreach($this->userRepository->findAll() as $user) {
                    $data[] = $user->toArray();
                }
                break;
            case 'posts':
                foreach($this->postRepository->findAll() as $post) {
                    $data[] = $post->toArray();
                }
                break;
            case 'surveys':
                foreach($this->surveyRepository->findAll() as $survey) {
                    $data[] = $survey->toArray();
                }
                break;
        }

        return $data;
    }

    public function adminDataList(Request $request): array
    {
        $tab = $request->query->get('p');
        $data = $this->getAdminData($tab);

        switch($tab) {
            case null:
                $tab = null;
                break;
            case 'users':
                break;
            case 'posts':
                break;
            case 'surveys':
                break;
            default:
                $tab = 'error';
                break;
        }

        return [
            'tab' => $tab,
            'data' => $data
        ];
    }
}