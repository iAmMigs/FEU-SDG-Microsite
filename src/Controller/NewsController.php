<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(ActivityRepository $activityRepository): Response
    {
        // Fetch all activities ordered by event date
        $activities = $activityRepository->findBy([], ['eventDate' => 'DESC']);

        return $this->render('SDG-Microsite/news/index.html.twig', [
            'activities' => $activities,
        ]);
    }
}