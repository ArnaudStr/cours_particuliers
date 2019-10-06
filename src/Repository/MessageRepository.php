<?php

namespace App\Repository;

use DateTime;
use App\Entity\Prof;
use App\Entity\Eleve;
// use Symfony\Component\Validator\Constraints\DateTime;
use App\Entity\Message;
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
    
        // returns an array of Product objects
        return $query->getOneorNullresult();
    }

    public function findConversation(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof);
    
        // returns an array of Product objects
        return $query->execute();
    }

    public function findNonLusConversationProf(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof
            AND m.auteur = :eleveUsername
            AND m.lu = 0'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof)
        ->setParameter('eleveUsername', $eleve->getUsername());
    
        // returns an array of Product objects
        return $query->execute();
    }

    public function findNonLusConversationEleve(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof
            AND m.auteur = :profUsername'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof)
        ->setParameter('profUsername', $prof->getUsername());
    
        // returns an array of Product objects
        return $query->execute();
    }
}
