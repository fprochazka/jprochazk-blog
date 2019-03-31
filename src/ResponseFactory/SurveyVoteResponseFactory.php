<?php


namespace App\ResponseFactory;

use App\Entity\SurveyOption;

class SurveyVoteResponseFactory
{
    public function getSurveyVoteJson(int $survey_option_id)
    {
        return [
            'status' => 'OK',
            'message' => [
                'vote_id' => $survey_option_id
            ],
            200
        ];
    }
}