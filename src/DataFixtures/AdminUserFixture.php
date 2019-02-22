<?php

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminUserFixture extends Fixture
{
    /** @var UserPasswordEncoderInterface */
	private $encoder;

	public function __construct(UserPasswordEncoderInterface $encoder)
	{
		$this->encoder = $encoder;
	}


    public function load(ObjectManager $manager): self
    {
        $user = new Person();
        $user->setUsername('admin');
        $user->setPassword(
        	$this->encoder->encodePassword($user, 'admin')
        );
        $user->setRole('ROLE_ADMIN');

        $manager->persist($user);
        $manager->flush();

        return $this;
    }
}
