<?php

namespace App\Repository;

use App\Entity\CountryVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CountryVisit>
 */
class CountryVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CountryVisit::class);
    }

    /**
     * Increments the visit count for a given country name and code safely.
     */
    public function incrementCountryVisit(string $countryName, string $countryCode = 'PH'): void
    {
        $em = $this->getEntityManager();
        $countryVisit = $this->findOneBy(['countryName' => $countryName]);

        if (!$countryVisit) {
            $countryVisit = new CountryVisit();
            $countryVisit->setCountryName($countryName);
            $countryVisit->setCountryCode($countryCode);
            $countryVisit->setVisitCount(1);
            $em->persist($countryVisit);
        } else {
            $countryVisit->incrementVisitCount();
        }

        $em->flush();
    }

    /**
     * Returns array of country visit totals sorted by visitCount DESC
     */
    public function getVisitsByCountrySummary(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.visitCount', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns total visits across all countries combined
     */
    public function getTotalVisitsCount(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.visitCount) as total')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
