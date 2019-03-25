<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function getAdminRole(): Role
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.name LIKE :p')
            ->setParameter('p', '%'.'ROLE_ADMIN'.'%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUserRole(): Role
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.name LIKE :p')
            ->setParameter('p', '%'.'ROLE_USER'.'%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
