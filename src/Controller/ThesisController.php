<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThesisController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(Request $request): Response
    {
        // Fetch the selected goals from the URL query parameters
        // Example: /library?goals[]=3&goals[]=7
        $selectedGoals = $request->query->all('goals');

        return $this->render('thesis/index.html.twig', [
            'selected_goals' => $selectedGoals,
        ]);
    }
}