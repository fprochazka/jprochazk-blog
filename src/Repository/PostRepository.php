<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @param int $offset
     * @param int $count
     * @return Post[]
     */
    public function findAllByOffsetCount(int $offset, int $count = 0) {
        return $this->createQueryBuilder('p')
            ->orderBy('p.subtime', 'DESC')
            ->getQuery()
            ->setFirstResult(($offset-1))
            ->setMaxResults($count)
            ->getResult();
    }

    /**
     * @param string $content
     * @param int $count
     * @param int $offset
     * @return Post[]
     */
    public function findByContent(string $content, int $count = 10, int $offset = 0) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.content LIKE :str')
            ->setParameter('str', '%'.$content.'%')
            ->orderBy('p.subtime', 'DESC')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($count)
            ->getResult();
    }

    /**
     * @param string $title
     * @param int $count
     * @param int $offset
     * @return Post[]
     */
    public function findByTitle(string $title, int $count = 10, int $offset = 0) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.title LIKE :str')
            ->setParameter('str', '%'.$title.'%')
            ->orderBy('p.subtime', 'DESC')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($count)
            ->getResult();
    }
}
