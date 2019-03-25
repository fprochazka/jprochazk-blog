<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Security\SecurityUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AB_AdminUserFixture extends Fixture
{
    /** @var UserPasswordEncoderInterface */
	private $encoder;

	private $roleRepo;

	public function __construct(UserPasswordEncoderInterface $encoder, RoleRepository $roleRepo)
	{
	    $this->roleRepo = $roleRepo;
		$this->encoder = $encoder;
	}


    public function load(ObjectManager $manager): self
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(
        	$this->encoder->encodePassword(new SecurityUser(new User()), 'admin')
        );
        $admin_role = $this->roleRepo->getAdminRole();
        $user->addRole($admin_role);

        $manager->persist($user);
        $manager->flush();

        return $this;
    }
}
