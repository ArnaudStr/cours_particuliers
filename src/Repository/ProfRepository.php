<?php

namespace App\Repository;

use App\Entity\Prof;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Prof|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prof|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prof[]    findAll()
 * @method Prof[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Prof::class);
    }


    /**
     * @param string $term
     * @return Result[]
     */
    public function findAllWithSearch(string $term)
    {
        $Result = $this->createQueryBuilder('r');
            $Result->andWhere('r.nom LIKE :term')
                ->setParameter('term', '%' .$term. '%');    
        
                return $Result
                    ->orderBy('r.nom', 'DESC')
                    ->getQuery()
                    ->getResult();
        
    }

    /**
    * @return Prof[] Returns an array of Prof objects
    */
    public function findBestFive()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.noteMoyenne', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Prof[] Returns an array of Prof objects
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
    public function findOneBySomeField($value): ?Prof
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findOneById($id): ?Prof
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByEmail($email): ?Prof
    {
        return $this->createQueryBuilder('p')
        ->andWhere('p.email = :email')
        ->setParameter('email', $email)
        ->getQuery()
        ->getOneOrNullResult()
        ;
    }
}
