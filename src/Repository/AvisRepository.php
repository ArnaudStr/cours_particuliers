<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Prof;
use App\Entity\Eleve;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Avis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avis[]    findAll()
 * @method Avis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    // /**
    //  * @return Avis[] Returns an array of Avis objects
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
    public function findOneBySomeField($value): ?Avis
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
    * @return Avis[] Retourne les 5 meilleurs avis
    */
    public function findBestFive()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.note', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNoteMoyenne(Prof $prof)
    {
        return $this->createQueryBuilder('a')
            ->select("avg(a.note)")
            ->where('a.prof = :prof')
            ->setParameter('prof', $prof)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findAvis(Prof $prof, Eleve $eleve)
    {
        return $this->createQueryBuilder('a')
            ->where('a.prof = :prof')
            ->andWhere('a.eleve = :eleve')
            ->setParameter('prof', $prof)
            ->setParameter('eleve', $eleve)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

}
