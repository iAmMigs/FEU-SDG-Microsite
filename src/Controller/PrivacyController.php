<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrivacyController extends AbstractController
{
    #[Route('/privacy-policy', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('SDG-Microsite/privacy.html.twig');
    }

    #[Route('/terms-of-service', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('SDG-Microsite/terms.html.twig');
    }
}
