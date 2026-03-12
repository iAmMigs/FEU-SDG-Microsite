<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ActivityRepository $activityRepository): Response
    {
        // Fetch the 3 most recent activities, ordered by event date descending
        $latestActivities = $activityRepository->findBy([], ['eventDate' => 'DESC'], 3);

        return $this->render('SDG-Microsite/home/index.html.twig', [
            'latest_activities' => $latestActivities,
        ]);
    }
}