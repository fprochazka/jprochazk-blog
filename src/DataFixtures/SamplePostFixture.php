<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Post;
use App\Entity\Comment;

class SamplePostFixture extends Fixture
{
    public function load(ObjectManager $manager): self
    {
        $post = new Post();
        $post->setAuthor("admin");
        $post->setTitle("Sample post");
        $post->setContent("This is a sample post. You can edit or delete it! 
        <"."br".">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. 
        <"."br".">Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, 
        <"."br".">sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Morbi scelerisque luctus velit. 
        <"."br".">Suspendisse nisl. Etiam sapien elit, consequat eget, tristique non, venenatis quis, ante. 
        <"."br".">Vivamus luctus egestas leo. Fusce tellus odio, dapibus id fermentum quis, suscipit id erat. 
        <"."br".">Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. Cras elementum. Vivamus ac leo pretium faucibus.");
        $post->setSubtime(new \DateTimeImmutable());

        $comment = new Comment();
        $comment->setAuthor("admin");
        $comment->setContent("This is a sample comment. You can edit or delete it!");
        $comment->setDate(new \DateTimeImmutable());

        $post->addComment($comment);

        $manager->persist($post);
        $manager->persist($comment);
        $manager->flush();

        return $this;
    }
}
