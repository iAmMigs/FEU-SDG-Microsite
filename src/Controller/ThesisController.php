<?php

namespace App\Controller;

use App\Repository\ThesisRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Thesis;

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
                $qb->groupBy('t.id')
                   ->having('COUNT(DISTINCT s.id) = :goalCount')
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
        $thesis->incrementViews();
        $em->flush();

        return $this->render('SDG-Microsite/thesis/show.html.twig', [
            'thesis' => $thesis,
        ]);
    }
}