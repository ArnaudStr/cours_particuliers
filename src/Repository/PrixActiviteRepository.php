<?php

namespace App\Repository;

use App\Entity\PrixActivite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PrixActivite|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrixActivite|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrixActivite[]    findAll()
 * @method PrixActivite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrixActiviteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PrixActivite::class);
    }

    // /**
    //  * @return PrixActivite[] Returns an array of PrixActivite objects
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
    public function findOneBySomeField($value): ?PrixActivite
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
