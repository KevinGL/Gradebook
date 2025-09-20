<?php

namespace App\Repository;

use App\Entity\Appreciation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appreciation>
 */
class AppreciationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appreciation::class);
    }

    //    /**
    //     * @return Appreciation[] Returns an array of Appreciation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Appreciation
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySubjectStudent($subject, $student): array
    {
        return $this->createQueryBuilder("a")
            ->where("a.subject = :subject")
            ->setParameter("subject", $subject)
            ->andWhere("a.student = :student")
            ->setParameter("student", $student)
            ->getQuery()
            ->getResult();
    }

    public function findByStudentTrimester($student, $trimester): array
    {
        $appreciations = $this->createQueryBuilder("a")
            ->where("a.trimester = :trimester")
            ->setParameter("trimester", $trimester)
            ->andWhere("a.student = :student")
            ->setParameter("student", $student)
            ->getQuery()
            ->getResult();
        
        $res = [];

        foreach($appreciations as $a)
        {
            $res[$a->getSubject()->getName()] = $a->getText();
        }

        return $res;
    }
}
