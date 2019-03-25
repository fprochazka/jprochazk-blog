<?php


namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AA_RoleFixture extends Fixture
{
    public function load(ObjectManager $manager): self
    {
        $admin_role = new Role('ROLE_ADMIN');
        $user_role = new Role('ROLE_USER');

        $manager->persist($admin_role);
        $manager->persist($user_role);
        $manager->flush();

        return $this;
    }
}