<?php

namespace App\Controller;

use App\Repository\ThesisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TrendingController extends AbstractController
{
    #[Route('/trending', name: 'app_trending')]
    public function index(ThesisRepository $thesisRepository): Response
    {
        // Fetch top 6 theses, ordered by most views
        $trendingTheses = $thesisRepository->findBy([], ['views' => 'DESC'], 6);

        return $this->render('trending/index.html.twig', [
            'theses' => $trendingTheses,
        ]);
    }
}