<?php

namespace App\Controller;

use App\Repository\ThesisRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThesisController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(Request $request, ThesisRepository $thesisRepository): Response
    {
        $selectedGoals = $request->query->all('goals');
        $searchAuthor = $request->query->get('author');
        $searchTitle = $request->query->get('title');
        $searchKeyword = $request->query->get('keyword');
        
        // 1. Get the exclusive filter checkbox value (true if checked, false if not)
        $isExclusive = $request->query->getBoolean('exclusive', false);
        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $qb = $thesisRepository->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        // 2. Updated SDG Filter Logic
        if (!empty($selectedGoals)) {
            $qb->join('t.sdgGoals', 's')
               ->andWhere('s.goalNumber IN (:goals)')
               ->setParameter('goals', $selectedGoals);
            
            if ($isExclusive) {
                // If exclusive is checked, the thesis must have ALL selected goals.
                // We group by the thesis ID and ensure the count of matched goals equals the total number of goals the user selected.
                $qb->groupBy('t.id')
                   ->having('COUNT(DISTINCT s.goalNumber) = :goalCount')
                   ->setParameter('goalCount', count($selectedGoals));
            }
        }
        
        if (!empty($searchAuthor)) {
            $qb->andWhere('t.authors LIKE :author')->setParameter('author', '%' . $searchAuthor . '%');
        }
        if (!empty($searchTitle)) {
            $qb->andWhere('t.title LIKE :title')->setParameter('title', '%' . $searchTitle . '%');
        }
        if (!empty($searchKeyword)) {
            $qb->andWhere('t.description LIKE :keyword')->setParameter('keyword', '%' . $searchKeyword . '%');
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $paginator = new Paginator($qb);
        $totalCount = count($paginator);
        $totalPages = max(1, ceil($totalCount / $limit));

        return $this->render('SDG-Microsite/thesis/index.html.twig', [
            'selected_goals' => $selectedGoals,
            'is_exclusive' => $isExclusive, // Pass this to Twig so the checkbox stays checked!
            'theses' => $paginator,
            'search_author' => $searchAuthor,
            'search_title' => $searchTitle,
            'search_keyword' => $searchKeyword,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
        ]);
    }
}