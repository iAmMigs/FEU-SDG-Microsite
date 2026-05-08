<?php

namespace App\Repository;

use App\Entity\Thesis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Thesis>
 */
class ThesisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thesis::class);
    }

    /**
     * Finds the most viewed active theses that have at least one view.
     * 
     * @param int $limit
     * @return Thesis[]
     */
    public function findTrending(int $limit = 6): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.isActive = :active')
            ->andWhere('t.views > 0')
            ->setParameter('active', true)
            ->orderBy('t.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}