<?php

namespace App\Repository;

use App\Entity\Memoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Memoria|null find($id, $lockMode = null, $lockVersion = null)
 * @method Memoria|null findOneBy(array $criteria, array $orderBy = null)
 * @method Memoria[]    findAll()
 * @method Memoria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Memoria::class);
    }

    // /**
    //  * @return Memoria[] Returns an array of Memoria objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Memoria
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
