<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SdgRepository;

/**
 * Handles the display and filtering of news and activities.
 */
final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(Request $request, ActivityRepository $activityRepository, SdgRepository $sdgRepository): Response
    {
        $selectedSdgs = $request->query->all('goals');
        $dateRange = $request->query->get('date_range');
        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;

        /**
         * leftJoin and addSelect pre-fetches all associated SDGs in the initial query
         */
        $qb = $activityRepository->createQueryBuilder('a')
            ->leftJoin('a.sdgs', 'all_sdgs')
            ->addSelect('all_sdgs')
            ->where('a.isActive = :active')
            ->andWhere('a.publishAt IS NULL OR a.publishAt <= :now')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.eventDate', 'DESC');

        if ($dateRange) {
            $dateLimit = new \DateTime();
            if ($dateRange === '1_day') {
                $dateLimit->modify('-1 day');
            } elseif ($dateRange === '7_days') {
                $dateLimit->modify('-7 days');
            } elseif ($dateRange === '30_days') {
                $dateLimit->modify('-30 days');
            }
            
            $qb->andWhere('a.eventDate >= :dateLimit')
               ->setParameter('dateLimit', $dateLimit);
        }

        if (!empty($selectedSdgs)) {
            $qb->join('a.sdgs', 's')
               ->andWhere('s.id IN (:sdgs)')
               ->setParameter('sdgs', $selectedSdgs);
        }

        $paginator = new Paginator($qb, true);
        $totalCount = count($paginator);
        $totalPages = max(1, ceil($totalCount / $limit));

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $activities = $qb->getQuery()->getResult();
        $allSdgs = $sdgRepository->findBy([], ['id' => 'ASC']);

        return $this->render('SDG-Microsite/news/index.html.twig', [
            'activities' => $activities,
            'selected_goals' => $selectedSdgs,
            'date_range' => $dateRange,
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

        return $this->render('SDG-Microsite/news/show.html.twig', [
            'activity' => $activity,
        ]);
    }
}