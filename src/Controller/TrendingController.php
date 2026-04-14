<?php

namespace App\Controller;

use App\Repository\ThesisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the display of the most viewed research projects.
 */
final class TrendingController extends AbstractController
{
    #[Route('/trending', name: 'app_trending')]
    public function index(ThesisRepository $thesisRepository): Response
    {
        $trendingTheses = $thesisRepository->findBy(['isActive' => true], ['views' => 'DESC'], 6);

        return $this->render('SDG-Microsite/trending/index.html.twig', [
            'theses' => $trendingTheses,
        ]);
    }
}