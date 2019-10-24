<?php

namespace App\Repository;

use App\Entity\Simulador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Simulador|null find($id, $lockMode = null, $lockVersion = null)
 * @method Simulador|null findOneBy(array $criteria, array $orderBy = null)
 * @method Simulador[]    findAll()
 * @method Simulador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimuladorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Simulador::class);
    }

    // /**
    //  * @return Simulador[] Returns an array of Simulador objects
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
    public function findOneBySomeField($value): ?Simulador
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
