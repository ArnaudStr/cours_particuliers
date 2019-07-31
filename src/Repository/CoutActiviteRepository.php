<?php

namespace App\Repository;

use App\Entity\CoutActivite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CoutActivite|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoutActivite|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoutActivite[]    findAll()
 * @method CoutActivite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoutActiviteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CoutActivite::class);
    }

    // /**
    //  * @return CoutActivite[] Returns an array of CoutActivite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CoutActivite
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
