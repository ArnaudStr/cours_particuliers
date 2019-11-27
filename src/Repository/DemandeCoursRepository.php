<?php

namespace App\Repository;

use App\Entity\Seance;
use App\Entity\DemandeCours;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DemandeCours|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeCours|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeCours[]    findAll()
 * @method DemandeCours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeCoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeCours::class);
    }

    /**
     * @return DemandeCours[] Returns an array of DemandeCours objects
     */
    public function findBySeance(Seance $seance)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.seance = :seance')
            ->setParameter('seance', $seance)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return DemandeCours[] Returns an array of DemandeCours objects
     */
    public function findByEleve(Eleve $eleve)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.eleve = :eleve')
            ->setParameter('eleve', $eleve)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?DemandeCours
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
