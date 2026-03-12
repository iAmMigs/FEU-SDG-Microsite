<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SdgController extends AbstractController
{
    #[Route('/sdgs', name: 'app_sdgs')]
    public function index(): Response
    {
        return $this->render('SDG-Microsite/sdg/index.html.twig');
    }
}