<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\College;
use App\Repository\ActivityRepository;
use App\Repository\SdgRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the display and filtering of news and activities.
 */
final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(Request $request, ActivityRepository $activityRepository, SdgRepository $sdgRepository, EntityManagerInterface $em): Response
    {
        $selectedSdgs = $request->query->all('goals');
        $dateFilter = $request->query->get('date_filter', 'all');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        $selectedCollege = $request->query->get('college');
        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 6;

        /**
         * leftJoin and addSelect pre-fetches all associated SDGs in the initial query
         */
        $qb = $activityRepository->createQueryBuilder('a')
            ->leftJoin('a.sdgs', 'all_sdgs')
            ->leftJoin('a.college', 'c')
            ->addSelect('all_sdgs', 'c')
            ->where('a.isActive = :active')
            ->andWhere('a.publishAt IS NULL OR a.publishAt <= :now')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.eventDate', 'DESC');

        if ($selectedCollege) {
            $qb->andWhere('c.id = :collegeId')
               ->setParameter('collegeId', $selectedCollege);
        }

        if ($dateFilter && $dateFilter !== 'all' && $dateFilter !== 'date_range') {
            if ($dateFilter === 'past_year') {
                $pastYear = (int) (new \DateTime())->format('Y') - 1;
                $startOfYear = new \DateTime("$pastYear-01-01 00:00:00");
                $endOfYear = new \DateTime("$pastYear-12-31 23:59:59");
                
                $qb->andWhere('a.eventDate >= :pastYearStart')
                   ->andWhere('a.eventDate <= :pastYearEnd')
                   ->setParameter('pastYearStart', $startOfYear)
                   ->setParameter('pastYearEnd', $endOfYear);
            } else {
                $dateLimit = new \DateTime();
                if ($dateFilter === '1_day') {
                    $dateLimit->modify('-1 day');
                } elseif ($dateFilter === '7_days') {
                    $dateLimit->modify('-7 days');
                } elseif ($dateFilter === '30_days') {
                    $dateLimit->modify('-30 days');
                }
                
                $qb->andWhere('a.eventDate >= :dateLimit')
                   ->setParameter('dateLimit', $dateLimit);
            }
        }

        if ($startDate) {
            $qb->andWhere('a.eventDate >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate));
        }

        if ($endDate) {
            $qb->andWhere('a.eventDate <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
        }

        if (!empty($selectedSdgs)) {
            $qb->andWhere('EXISTS (
                SELECT 1 FROM App\Entity\Sdg s_sub 
                JOIN s_sub.activities a_sub 
                WHERE a_sub.id = a.id AND s_sub.id IN (:sdgs)
            )')
               ->setParameter('sdgs', $selectedSdgs);
        }

        $paginator = new Paginator($qb, true);
        $totalCount = count($paginator);
        $totalPages = max(1, ceil($totalCount / $limit));

        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $activities = iterator_to_array($paginator);
        $allSdgs = $sdgRepository->findBy([], ['id' => 'ASC']);
        $allColleges = $em->getRepository(College::class)->findBy([], ['name' => 'ASC']);

        return $this->render('SDG-Microsite/news.html.twig', [
            'activities' => $activities,
            'selected_goals' => $selectedSdgs,
            'date_filter' => $dateFilter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'selected_college' => $selectedCollege,
            'all_colleges' => $allColleges,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'all_sdgs' => $allSdgs, 
        ]);
    }

    #[Route('/news/article/{id}', name: 'app_news_show')]
    public function show(Activity $activity): Response
    {
        if (!$activity->isActive() || ($activity->getPublishAt() !== null && $activity->getPublishAt() > new \DateTime())) {
            throw $this->createNotFoundException('This article is not available.');
        }

        return $this->render('SDG-Microsite/news_view.html.twig', [
            'activity' => $activity,
        ]);
    }
}