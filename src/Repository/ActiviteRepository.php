<?php

namespace App\Repository;

use App\Entity\Activite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Activite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activite[]    findAll()
 * @method Activite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    /**
     * @param string $term
     * @return activite
     */
    public function findOneWithSearch(string $term)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nom LIKE :term')
            ->setParameter('term', $term)
            ->orderBy('a.nom', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $term
     * @return activite[]
     */
    public function findWithSearch(string $term)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nom LIKE :term')
            ->setParameter('term', '%'. $term .'%')
            ->orderBy('a.nom', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $term
     * @return activite[]
     */
    public function findNames()
    {
        return $this->createQueryBuilder('a')
            ->select('a.nom')
            ->getQuery()
            // ->getSingleScalarResult()
            ->getResult()
        ;
    }

    // /**
    //  * @return Activite[] Returns an array of Activite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Activite
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
