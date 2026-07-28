<?php

namespace App\Repository;

use App\Entity\SiteVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteVisit>
 */
class SiteVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteVisit::class);
    }

    /**
     * Gets aggregated visit counts grouped by country.
     *
     * @return array<int, array{countryCode: string, countryName: string, visitCount: int}>
     */
    public function getVisitsByCountrySummary(): array
    {
        /** @var array<int, array{countryCode: string, countryName: string, visitCount: int}> */
        return $this->createQueryBuilder('v')
            ->select('v.countryCode as countryCode', 'v.countryName as countryName', 'COUNT(v.id) as visitCount')
            ->groupBy('v.countryCode', 'v.countryName')
            ->orderBy('visitCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieves the most recent site visits.
     *
     * @return SiteVisit[]
     */
    public function getRecentVisits(int $limit = 10): array
    {
        return $this->findBy([], ['visitedAt' => 'DESC'], $limit);
    }
}
