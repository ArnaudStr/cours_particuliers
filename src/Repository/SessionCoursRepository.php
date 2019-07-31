<?php

namespace App\Repository;

use App\Entity\SessionCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SessionCours|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionCours|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionCours[]    findAll()
 * @method SessionCours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionCoursRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SessionCours::class);
    }

    // /**
    //  * @return SessionCours[] Returns an array of SessionCours objects
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
    public function findOneBySomeField($value): ?SessionCours
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
