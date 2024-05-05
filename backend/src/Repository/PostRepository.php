<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getTotalPostsPerDay(): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p.createdAt AS date')
            ->addSelect('COUNT(p.id) AS totalPosts')
            ->groupBy('date')
            ->getQuery();

        $results = $query->getResult();

        // Format the dates and counts
        $formattedData = [
            'dates' => [],
            'counts' => []
        ];

        foreach ($results as $result) {
            // Convert the datetime object to a string in 'Y-m-d' format
            $date = $result['date']->format('Y-m-d');

            $formattedData['dates'][] = $date;
            $formattedData['counts'][] = (int) $result['totalPosts'];
        }

        return $formattedData;
    }



    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
