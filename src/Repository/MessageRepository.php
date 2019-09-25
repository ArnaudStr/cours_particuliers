<?php

namespace App\Repository;

use DateTime;
use App\Entity\Eleve;
use App\Entity\Message;
// use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    // /**
    //  * @return Message[] Returns an array of Message objects
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
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllToDelete(): array
    {
        $date = new DateTime('now -30 days');
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        return $this->createQueryBuilder('m')
            ->andWhere('m.dateEnvoi < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

    }

    public function findNonLusEleve(Eleve $eleve): array
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT count(m)
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.auteur != :eleveUsername
            AND m.lu = 0'
        )->setParameter('eleve', $eleve)
        ->setParameter('eleveUsername', $eleve->getUsername());
    
        // dd($query->execute());
        // returns an array of Product objects
        return $query->getOneorNullresult();

        // $qb = $this->createQueryBuilder('m')
        //         ->andWhere('m.eleve = :eleve')
        //         ->andWhere('m.auteur != :eleveUsername')
        //         ->andWhere('m.lu = 0')
                // ->setParameter('eleveId', $eleve->getId())
                // ->setParameter('eleveId', $eleve->getUsername())
        //         ->getQuery()
        //         ->getResult();

        // return count($qb);

    }
}
