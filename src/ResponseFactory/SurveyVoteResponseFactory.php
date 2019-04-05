<?php


namespace App\ResponseFactory;

class SurveyVoteResponseFactory
{
    public function getSurveyVoteJson(int $survey_option_id): array
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