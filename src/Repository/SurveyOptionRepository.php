<?php

namespace App\Repository;

use App\Entity\SurveyOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SurveyOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyOption[]    findAll()
 * @method SurveyOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyOptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SurveyOption::class);
    }

    // /**
    //  * @return SurveyOption[] Returns an array of SurveyOption objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SurveyOption
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
