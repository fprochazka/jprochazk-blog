<?php


namespace App\DTO;


class SurveyVoteDto
{
    /** @var int */
    private $survey_id;

    /** @var int */
    private $option_id;

    public function __construct
    (
        int $survey_id,
        int $option_id
    )
    {
        $this->survey_id = $survey_id;
        $this->option_id = $option_id;
    }

    public function getSurveyId(): int
    {
        return $this->survey_id;
    }

    public function getOptionId(): int
    {
        return $this->option_id;
    }
}