<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TrendingController extends AbstractController
{
    #[Route('/trending', name: 'app_trending')]
    public function index(): Response
    {
        return $this->render('trending/index.html.twig', [
            'controller_name' => 'TrendingController',
        ]);
    }
}
