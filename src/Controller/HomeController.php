<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use App\Repository\ThesisRepository; // 1. Add this import
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ActivityRepository $activityRepository, ThesisRepository $thesisRepository): Response
    {
        $latestActivities = $activityRepository->findBy([], ['eventDate' => 'DESC'], 3);

        $totalTheses = $thesisRepository->count([]);

        return $this->render('SDG-Microsite/home/index.html.twig', [
            'latest_activities' => $latestActivities,
            'total_theses' => $totalTheses,
        ]);
    }
}