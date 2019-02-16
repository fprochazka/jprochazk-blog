<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Survey;
use App\Entity\SurveyOption;

class SampleSurveyFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $survey = new Survey();
        
        $firstOption = new SurveyOption();
        $secondOption = new SurveyOption();
        $thirdOption = new SurveyOption();

        $firstOption->setTitle("Yes");
        $firstOption->setVotes(10);

        $secondOption->setTitle("No");
        $secondOption->setVotes(5);

        $thirdOption->setTitle("Maybe");
        $thirdOption->setVotes(3);

        $survey
            ->setTitle("To be, or not to be?")
            ->addOption($firstOption)
            ->addOption($secondOption)
            ->addOption($thirdOption)
            ->unlock();


        $manager->persist($firstOption);
        $manager->persist($secondOption);
        $manager->persist($thirdOption);
        $manager->persist($survey);
        $manager->flush();
    }
}
