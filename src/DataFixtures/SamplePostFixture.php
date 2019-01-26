<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Post;
use App\Entity\Comment;

class SamplePostFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $date = date_create_from_format('H:i:s Y-m-d', date('H:i:s Y-m-d'));

    	$post = new Post();
    	$comment = new Comment();

    	//sample post
    	$post->setTitle('Sample post');
    	$post->setContent('Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquid ex ea commodi consequat. Quis aute iure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
    	$post->setSubtime($date);
    	$post->setAuthor('System');

    	//sample comment
    	$comment->setContent('Lorem ipsum dolor sit amet. This is a comment.');
    	$comment->setAuthor('System');
    	$comment->setDate($date);
    	$post->addComment($comment);

    	//save
    	$manager->persist($post);
    	$manager->persist($comment);
        $manager->flush();
    }
}
