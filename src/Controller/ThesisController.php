<?php

namespace App\Controller;

use App\Entity\Thesis;
use App\Repository\ThesisRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        
        $isExclusive = $request->query->getBoolean('exclusive', false);
        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $qb = $thesisRepository->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        if (!empty($selectedGoals)) {
            $qb->join('t.sdgs', 's')
               ->andWhere('s.id IN (:goals)')
               ->setParameter('goals', $selectedGoals);
            
            if ($isExclusive) {
                // Must contain ALL selected goals
                $qb->groupBy('t.id')
                   ->having('COUNT(DISTINCT s.id) = :goalCount')
                   ->setParameter('goalCount', count($selectedGoals));
                   
                // RECISION MATCH: It must NOT contain any goals outside of the selected ones.
                // We use a sub-query to check if this specific thesis has any goals that aren't in the selected array.
                $qb->andWhere(
                    $qb->expr()->not(
                        $qb->expr()->exists(
                            'SELECT 1 FROM App\Entity\Thesis t2 JOIN t2.sdgs s2 WHERE t2.id = t.id AND s2.id NOT IN (:goals)'
                        )
                    )
                );
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
            'is_exclusive' => $isExclusive,
            'theses' => $paginator,
            'search_author' => $searchAuthor,
            'search_title' => $searchTitle,
            'search_keyword' => $searchKeyword,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
        ]);
    }

    #[Route('/library/article/{id}', name: 'app_thesis_show')]
    public function show(Thesis $thesis, EntityManagerInterface $em): Response
    {
        // Automatically increment the view count every time this route is hit
        $thesis->incrementViews();
        $em->flush();

        return $this->render('SDG-Microsite/thesis/show.html.twig', [
            'thesis' => $thesis,
        ]);
    }
}