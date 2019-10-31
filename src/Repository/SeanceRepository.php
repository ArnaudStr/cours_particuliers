<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\Eleve;
use App\Entity\Prof;
use App\Entity\Seance;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Seance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Seance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Seance[]    findAll()
 * @method Seance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Seance::class);
    }

    // /**
    //  * @return Seance[] Returns an array of Seance objects
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

    // Prochain séance d'un élève
    public function findNextSeanceEleve(Eleve $eleve, Cours $cours) {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT s
            FROM App\Entity\Seance s
            WHERE s.eleve = :eleve
            AND s.cours = :cours
            AND s.dateDebut > CURRENT_TIMESTAMP()'
        )->setParameter('eleve', $eleve)
        ->setParameter('cours', $cours)
        ->setMaxResults(1);
    
        return $query->getOneorNullresult();
    }


    // /**
    //  * @return Seance[] Seances à supprimer lorsqu'un prof modifie ses disponibilités
    //  */
    public function findToDelete($jour, $debut, $fin, Prof $prof)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.prof = :prof')
            ->andWhere("DATE_FORMAT(s.dateDebut, '%a') = :jour")
            ->andWhere("DATE_FORMAT(s.dateDebut, '%H') BETWEEN :debut AND :fin")
            ->andWhere('s.eleve IS NULL')
            ->setParameter('prof', $prof)
            ->setParameter('jour', $jour)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getResult()
        ;
    }

 
    // /**
    //  * @return Seance[] Seances libres antérieures à la date actuelle (seances à effacer)
    //  */
    public function findSeancesLibresPassees() {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT s
            FROM App\Entity\Seance s
            WHERE s.eleve IS NULL
            AND s.dateDebut < CURRENT_TIMESTAMP()'
        );
    
        return $query->execute();
    }
    /*
    public function findOneBySomeField($value): ?Seance
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
