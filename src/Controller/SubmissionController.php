<?php

namespace App\Controller;

use App\Entity\SubmissionRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the Project Submission instructions page.
 */
final class SubmissionController extends AbstractController
{
    #[Route('/submit-project', name: 'app_submit_project')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $requirementRepository = $entityManager->getRepository(SubmissionRequirement::class);

        $projectRequirements = $requirementRepository->findBy(
            ['type' => 'project'],
            ['sortOrder' => 'ASC']
        );

        $eventRequirements = $requirementRepository->findBy(
            ['type' => 'event'],
            ['sortOrder' => 'ASC']
        );

        return $this->render('SDG-Microsite/submit.html.twig', [
            'projectRequirements' => $projectRequirements,
            'eventRequirements' => $eventRequirements,
        ]);
    }
}
