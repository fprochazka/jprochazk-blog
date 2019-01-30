<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Survey;

class SampleSurveyFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $survey = new Survey();

        $options = [1 => [
        				"name" => "Yes",
        				"votes" => 10
        			], 
        			2 => [
        				"name" => "No", 
        				"votes" => 5
        			], 
        			3 => [
        				"name" => "Maybe", 
        				"votes" => 3
        			]
        ];

        $locked = false;
        
        //sample survey
        //options layout is:
        /*
        [ "option 1" => number_of_votes,
          "option 2" => number_of_votes,
          ...
          "option n" => number_of_votes ];
          */

        $survey->setTitle("To be, or not to be?");
        $survey->setOptions($options);
        $survey->setLocked($locked);

        $manager->persist($survey);
        $manager->flush();
    }
}
