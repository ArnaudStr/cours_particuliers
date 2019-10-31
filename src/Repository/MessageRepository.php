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

    public function deleteMessagesNbJours($nbJours){
        $date = new DateTime('now -'.$nbJours.' days');

        return $this->createQueryBuilder('m')
            ->andWhere('m.dateEnvoi < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function findNbNonLusEleve(Eleve $eleve) {
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
        return $query->getSingleScalarResult();
    }

    
    public function findNbNonLusProf(Prof $prof)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT count(m)
            FROM App\Entity\Message m
            WHERE m.prof = :prof
            AND m.auteur != :profUsername
            AND m.lu = 0'
        )->setParameter('prof', $prof)
        ->setParameter('profUsername', $prof->getUsername());
    
        // returns an array of Product objects
        return $query->getSingleScalarResult();
    }

    // Toutes les conversations d'un prof
    public function findAllConversationsProf(Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.prof = :prof
            GROUP BY m.eleve'
        )->setParameter('prof', $prof);
    
        return $query->execute();
    }

    // Toutes les conversations d'un eleve
    public function findAllConversationsEleve(Eleve $eleve): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            GROUP BY m.prof'
        )->setParameter('eleve', $eleve);
    
        return $query->execute();
    }

    // Nombre de messages non lu et messages d'un prof pour chaque eleve
    public function findNbNonLusProfEleve(Prof $prof, Eleve $eleve)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT count(m)
            FROM App\Entity\Message m
            WHERE m.prof = :prof
            AND m.eleve = :eleve
            AND m.auteur != :profUsername
            AND m.lu = 0'
        )->setParameter('prof', $prof)
        ->setParameter('eleve', $eleve)
        ->setParameter('profUsername', $prof->getUsername());
    
        // returns an array of Product objects
        return $query->getSingleScalarResult();
    }

    // Nombre de messages non lu et messages d'un eleve pour chaque prof
    public function findNbNonLusEleveProf(Prof $prof, Eleve $eleve)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT count(m)
            FROM App\Entity\Message m
            WHERE m.prof = :prof
            AND m.eleve = :eleve
            AND m.auteur != :eleveUsername
            AND m.lu = 0'
        )->setParameter('prof', $prof)
        ->setParameter('eleve', $eleve)
        ->setParameter('eleveUsername', $eleve->getUsername());
    
        // returns an array of Product objects
        return $query->getSingleScalarResult();
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
    
        // tous les messages entre un prof et élève
        return $query->execute();
    }

    // renvoie les messages lus envoyés par l'élève au prof
    public function findConversationLusProf(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof
            AND m.auteur = :eleveUsername
            AND m.lu = 1'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof)
        ->setParameter('eleveUsername', $eleve->getUsername());
    
        return $query->execute();
    }

    // renvoie les messages lus envoyés par le prof à l'éleve
    public function findConversationLusEleve(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof
            AND m.auteur = :profUsername
            AND m.lu = 1'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof)
        ->setParameter('profUsername', $prof->getUsername());
    
        return $query->execute();
    }


    // renvoie les messages non lus envoyés par l'élève au prof
    public function findConversationNonLusProf(Eleve $eleve, Prof $prof): array
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
    
        return $query->execute();
    }

    // renvoie les messages non lus envoyés par l'élève au prof
    public function findConversationNonLusEleve(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof
            AND m.auteur = :profUsername
            AND m.lu = 0'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof)
        ->setParameter('profUsername', $prof->getUsername());
    
        return $query->execute();
    }

    // renvoie les messages envoyés du prof à l'élève
    public function findConversationEnvoyesProf(Eleve $eleve, Prof $prof): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Message m
            WHERE m.eleve = :eleve
            AND m.prof = :prof
            AND m.auteur = :profUsername
            AND m.lu = 0'
        )->setParameter('eleve', $eleve)
        ->setParameter('prof', $prof)
        ->setParameter('profUsername', $prof->getUsername());
    
        return $query->execute();
    }

    // renvoie les messages envoyés de l'élève au prof
    public function findConversationEnvoyesEleve(Eleve $eleve, Prof $prof): array
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
    
        return $query->execute();
    }


}
