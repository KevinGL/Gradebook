<?php

namespace App\Repository;

use App\Entity\SchoolClass;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBySchoolClass(SchoolClass $class): array
    {
        return $this->createQueryBuilder("u")
            ->where("u.class = :class")
            ->setParameter("class", $class)
            ->getQuery()
            ->getResult();
    }

    public function findTeachers(int $page): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_TEACHER%')
            ->setFirstResult(($page - 1) * $_ENV["LIMIT_PAGE"])
            ->setMaxResults($_ENV["LIMIT_PAGE"])
            ->getQuery()
            ->getResult();
    }

    public function findNbPagesTeachers(): int
    {
        $teachers = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_TEACHER%')
            ->getQuery()
            ->getResult();
        
        return round(count($teachers) / $_ENV["LIMIT_PAGE"]) + 1;
    }

    public function findStudents(int $page): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_STUDENT%')
            ->setFirstResult(($page - 1) * $_ENV["LIMIT_PAGE"])
            ->setMaxResults($_ENV["LIMIT_PAGE"])
            ->getQuery()
            ->getResult();
    }

    public function findNbPagesStudents(): int
    {
        $teachers = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_STUDENT%')
            ->getQuery()
            ->getResult();
        
        return round(count($teachers) / $_ENV["LIMIT_PAGE"]) + 1;
    }
}
