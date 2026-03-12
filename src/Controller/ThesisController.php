<?php

namespace App\Controller;

use App\Repository\ThesisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThesisController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(Request $request, ThesisRepository $thesisRepository): Response
    {
        // 1. Fetch all filter parameters from the request
        $selectedGoals = $request->query->all('goals');
        $searchAuthor = $request->query->get('author');
        $searchTitle = $request->query->get('title');
        $searchKeyword = $request->query->get('keyword');

        // 2. Start building the query
        $qb = $thesisRepository->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        // Filter by SDGs
        if (!empty($selectedGoals)) {
            $qb->join('t.sdgGoals', 's')
               ->andWhere('s.goalNumber IN (:goals)')
               ->setParameter('goals', $selectedGoals);
        }

        // Filter by Author
        if (!empty($searchAuthor)) {
            $qb->andWhere('t.authors LIKE :author')
               ->setParameter('author', '%' . $searchAuthor . '%');
        }

        // Filter by Title
        if (!empty($searchTitle)) {
            $qb->andWhere('t.title LIKE :title')
               ->setParameter('title', '%' . $searchTitle . '%');
        }

        // Filter by Keyword (scans the description/abstract)
        if (!empty($searchKeyword)) {
            $qb->andWhere('t.description LIKE :keyword')
               ->setParameter('keyword', '%' . $searchKeyword . '%');
        }

        $theses = $qb->getQuery()->getResult();

        return $this->render('thesis/index.html.twig', [
            'selected_goals' => $selectedGoals,
            'theses' => $theses,
            // Pass these back so the input fields don't clear out while typing
            'search_author' => $searchAuthor,
            'search_title' => $searchTitle,
            'search_keyword' => $searchKeyword,
        ]);
    }
}