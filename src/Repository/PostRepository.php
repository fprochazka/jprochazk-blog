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

    public function findAllByOffsetCount(int $offset, int $count = 0): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.subtime', 'DESC')
            ->getQuery()
            ->setFirstResult(($offset-1)*10)
            ->setMaxResults($count)
            ->getResult();
    }

    public function findByContent(string $content, int $offset = 0, int $count = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.content LIKE :str')
            ->setParameter('str', '%'.$content.'%')
            ->orderBy('p.subtime', 'DESC')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($count)
            ->getResult();
    }

    public function findByTitle(string $title, int $offset = 0, int $count = 10): array
    {
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
