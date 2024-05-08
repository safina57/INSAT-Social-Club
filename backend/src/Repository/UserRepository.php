<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function addToken($data,$token, $entityManager) : bool
    {
        $user = null;
        if(isset($data['email'])) {
            $user = $this->findOneBy(['email' => $data['email']]);
            $user->setResetPasswordToken($token);
        }
        elseif(isset($data['username'])){
            $user = $this->findOneBy(['username' => $data['username']]);
            $user->setRememberMeToken($token);
        }
        $entityManager->persist($user);
        $entityManager->flush();
        return true;
    }
    public function userExist($username, $email) : bool
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->orWhere('u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $email)
            ->getQuery();
        $result = $query->getOneOrNullResult();
        return $result !== null;
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
}
