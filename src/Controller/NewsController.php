<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(ActivityRepository $activityRepository): Response
    {
        /* * Limits the fetched activities to a maximum of 12 records.
         * This prevents PHP-CGI memory exhaustion on the server when the table grows large.
         */
        $activities = $activityRepository->findBy([], ['eventDate' => 'DESC'], 12);

        return $this->render('SDG-Microsite/news/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    #[Route('/news/article/{id}', name: 'app_news_show')]
    public function show(Activity $activity): Response
    {
        return $this->render('SDG-Microsite/news/show.html.twig', [
            'activity' => $activity,
        ]);
    }
}