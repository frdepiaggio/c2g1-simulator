<?php

namespace App\Repository;

use App\Entity\Particion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Particion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Particion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Particion[]    findAll()
 * @method Particion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Particion::class);
    }

    // /**
    //  * @return Particion[] Returns an array of Particion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Particion
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
