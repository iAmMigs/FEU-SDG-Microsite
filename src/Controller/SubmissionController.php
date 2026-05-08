<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the Project Submission instructions page.
 */
final class SubmissionController extends AbstractController
{
    #[Route('/submit-project', name: 'app_submit_project')]
    public function index(): Response
    {
        return $this->render('SDG-Microsite/submit.html.twig', [
            'controller_name' => 'SubmissionController',
        ]);
    }
}
