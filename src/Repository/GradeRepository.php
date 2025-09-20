<?php

namespace App\Repository;

use App\Entity\Grade;
use App\Entity\Subject;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grade>
 */
class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    //    /**
    //     * @return Grade[] Returns an array of Grade objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Grade
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySubjectStudent(Subject $subject, User $student, int $trimester): array
    {
        $limitDates = [];

        if($trimester == 1)
        {
            $d1 = new \DateTime("01-09-2024T08:00:00");
            $d2 = new \DateTime("31-12-2024T16:00:00");
            
            $limitDates = [$d1, $d2];
        }

        else
        if($trimester == 2)
        {
            $d1 = new \DateTime("01-01-2025T08:00:00");
            $d2 = new \DateTime("31-03-2025T16:00:00");

            $limitDates = [$d1, $d2];
        }

        else
        {
            $d1 = new \DateTime("01-04-2025T08:00:00");
            $d2 = new \DateTime("30-06-2025T16:00:00");

            $limitDates = [$d1, $d2];
        }
        
        return $this->createQueryBuilder("g")
            ->where("g.subject = :subject")
            ->andWhere("g.student = :student")            
            ->andWhere("g.date BETWEEN :date1 AND :date2")
            ->setParameter("subject", $subject)
            ->setParameter("student", $student)
            ->setParameter("date1", $limitDates[0])
            ->setParameter("date2", $limitDates[1])
            ->getQuery()
            ->getResult();
    }

    public function findForStudentByTrimester(User $student, int $trimester): array
    {
        $limitDates = [];

        if($trimester == 1)
        {
            $d1 = new \DateTime("01-09-2024T08:00:00");
            $d2 = new \DateTime("31-12-2024T16:00:00");
            
            $limitDates = [$d1, $d2];
        }

        else
        if($trimester == 2)
        {
            $d1 = new \DateTime("01-01-2025T08:00:00");
            $d2 = new \DateTime("31-03-2025T16:00:00");

            $limitDates = [$d1, $d2];
        }

        else
        {
            $d1 = new \DateTime("01-04-2025T08:00:00");
            $d2 = new \DateTime("30-06-2025T16:00:00");

            $limitDates = [$d1, $d2];
        }
        
        $grades = $this->createQueryBuilder("g")
            ->where("g.student = :student")
            ->andWhere("g.date BETWEEN :date1 AND :date2")
            ->setParameter("student", $student)
            ->setParameter("date1", $limitDates[0])
            ->setParameter("date2", $limitDates[1])
            ->getQuery()
            ->getResult();

        $res = [];

        $subjectsNames =
        [
            "Mathématiques",
            "Français",
            "Histoire-géographie",
            "Anglais",
            "Italien",
            "Physique-chimie"
        ];

        foreach($subjectsNames as $s)
        {
            $res[$s] = 0.0;
            $count = 0;

            foreach($grades as $g)
            {
                if($g->getSubject()->getName() == $s)
                {
                    $res[$s] += $g->getValue();
                    $count++;
                }
            }

            if($count > 0)
            {
                $res[$s] /= $count;
            }

            $res[$s] = round($res[$s], 2);
        }

        return $res;
    }
}
