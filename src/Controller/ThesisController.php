<?php

namespace App\Controller;

use App\Entity\Thesis;
use App\Repository\ThesisRepository;
use App\Repository\SdgRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThesisController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(Request $request, ThesisRepository $thesisRepository, SdgRepository $sdgRepository): Response
    {
        // 1. Get search parameters
        $searchAuthor = $request->query->get('author', '');
        $searchTitle = $request->query->get('title', '');
        $searchKeyword = $request->query->get('keyword', '');
        $selectedSdgs = $request->query->all('goals');
        $isExclusive = $request->query->getBoolean('exclusive', false);
        
        // 2. Pagination setup
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        // 3. Base Query
        $qb = $thesisRepository->createQueryBuilder('t')
            ->orderBy('t.publicationDate', 'DESC');

        if ($searchAuthor) {
            $qb->andWhere('t.authors LIKE :author')
               ->setParameter('author', '%' . $searchAuthor . '%');
        }

        if ($searchTitle) {
            $qb->andWhere('t.title LIKE :title')
               ->setParameter('title', '%' . $searchTitle . '%');
        }

        if ($searchKeyword) {
            $qb->andWhere('t.description LIKE :keyword')
               ->setParameter('keyword', '%' . $searchKeyword . '%');
        }

        if (!empty($selectedSdgs)) {
            if ($isExclusive) {
                // Precision search: Must have ALL selected SDGs
                $qb->join('t.sdgs', 's')
                   ->groupBy('t.id')
                   ->having('COUNT(s.id) = :count')
                   ->andWhere('s.id IN (:sdgs)')
                   ->setParameter('sdgs', $selectedSdgs)
                   ->setParameter('count', count($selectedSdgs));
            } else {
                // Normal search: Has ANY of the selected SDGs
                $qb->join('t.sdgs', 's')
                   ->andWhere('s.id IN (:sdgs)')
                   ->setParameter('sdgs', $selectedSdgs);
            }
        }

        // 4. Execute Pagination
        $paginator = new Paginator($qb);
        $totalCount = count($paginator);
        $totalPages = ceil($totalCount / $limit) ?: 1;

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $theses = $qb->getQuery()->getResult();

        // 5. FETCH ONLY ACTIVE SDGs FOR THE SIDEBAR FILTER
        $activeSdgs = $sdgRepository->findBy(['isActive' => true], ['id' => 'ASC']);

        return $this->render('SDG-Microsite/library/index.html.twig', [
            'theses' => $theses,
            'search_author' => $searchAuthor,
            'search_title' => $searchTitle,
            'search_keyword' => $searchKeyword,
            'selected_goals' => $selectedSdgs,
            'is_exclusive' => $isExclusive,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'active_sdgs' => $activeSdgs, // Sent to template
        ]);
    }

    #[Route('/library/thesis/{id}', name: 'app_thesis_show')]
    public function show(Thesis $thesis): Response
    {
        return $this->render('SDG-Microsite/library/show.html.twig', [
            'thesis' => $thesis,
        ]);
    }
}