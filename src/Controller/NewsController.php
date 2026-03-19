<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(Request $request, ActivityRepository $activityRepository): Response
    {
        // 1. Get filter parameters from the URL
        $searchTitle = $request->query->get('title');
        $searchCategory = $request->query->get('category');
        $searchYear = $request->query->get('year');
        $selectedSdgs = $request->query->all('goals'); // Array of SDG IDs

        // 2. Build the query to ONLY show active and published articles
        $qb = $activityRepository->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->andWhere('a.publishAt IS NULL OR a.publishAt <= :now')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.eventDate', 'DESC');

        // 3. Apply Title Search
        if ($searchTitle) {
            $qb->andWhere('a.title LIKE :title')
               ->setParameter('title', '%' . $searchTitle . '%');
        }

        // 4. Apply Category Search
        if ($searchCategory) {
            $qb->andWhere('a.category LIKE :category')
               ->setParameter('category', '%' . $searchCategory . '%');
        }

        // 5. Apply Year Filter (Safe Doctrine Date Comparison)
        if ($searchYear) {
            $startDate = new \DateTime("$searchYear-01-01 00:00:00");
            $endDate = new \DateTime("$searchYear-12-31 23:59:59");
            
            $qb->andWhere('a.eventDate BETWEEN :startYear AND :endYear')
               ->setParameter('startYear', $startDate)
               ->setParameter('endYear', $endDate);
        }

        // 6. Apply SDG Filter
        if (!empty($selectedSdgs)) {
            $qb->join('a.sdgs', 's')
               ->andWhere('s.id IN (:sdgs)')
               ->setParameter('sdgs', $selectedSdgs);
        }

        $activities = $qb->getQuery()->getResult();

        return $this->render('SDG-Microsite/news/index.html.twig', [
            'activities' => $activities,
            'search_title' => $searchTitle,
            'search_category' => $searchCategory,
            'search_year' => $searchYear,
            'selected_goals' => $selectedSdgs,
        ]);
    }

    #[Route('/news/article/{id}', name: 'app_news_show')]
    public function show(Activity $activity): Response
    {
        // Optional security: Prevent direct URL access to unpublished posts unless admin
        if (!$activity->isActive() || ($activity->getPublishAt() !== null && $activity->getPublishAt() > new \DateTime())) {
            // You can throw a 404 here if a normal user tries to access a hidden link
            throw $this->createNotFoundException('This article is not available.');
        }

        return $this->render('SDG-Microsite/news/show.html.twig', [
            'activity' => $activity,
        ]);
    }
}