<?php

namespace App\Controller;

use App\Entity\SubmissionCategory;
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
        $categoryRepository = $entityManager->getRepository(SubmissionCategory::class);

        $categories = $categoryRepository->findBy([], ['id' => 'ASC']);

        return $this->render('SDG-Microsite/submit.html.twig', [
            'categories' => $categories,
        ]);
    }
}
