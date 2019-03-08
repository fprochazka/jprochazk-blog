<?php


namespace App\Facade;


use App\Repository\PersonRepository;
use App\Repository\PostRepository;
use App\Repository\SurveyRepository;
use Symfony\Component\HttpFoundation\Request;

class AdminFacade
{
    /** @var PersonRepository  */
    private $personRepository;
    /** @var PostRepository  */
    private $postRepository;
    /** @var SurveyRepository  */
    private $surveyRepository;

    public function __construct(
        PersonRepository $personRepository,
        PostRepository $postRepository,
        SurveyRepository $surveyRepository
    )
    {
        $this->personRepository = $personRepository;
        $this->postRepository = $postRepository;
        $this->surveyRepository = $surveyRepository;
    }

    private function getAdminData(?string $tab): ?array
    {
        $data = null;
        switch($tab) {
            case 'users':
                foreach($this->personRepository->findAll() as $user) {
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