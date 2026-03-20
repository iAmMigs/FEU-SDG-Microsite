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
        $searchAuthor = $request->query->get('author', '');
        $searchTitle = $request->query->get('title', '');
        $searchKeyword = $request->query->get('keyword', '');
        $selectedSdgs = $request->query->all('goals');
        $isExclusive = $request->query->getBoolean('exclusive', false);
        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // BUG FIX: Changed 'publicationDate' to 'createdAt' to match your Entity
        $qb = $thesisRepository->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

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
                $qb->join('t.sdgs', 's')
                   ->groupBy('t.id')
                   ->having('COUNT(s.id) = :count')
                   ->andWhere('s.id IN (:sdgs)')
                   ->setParameter('sdgs', $selectedSdgs)
                   ->setParameter('count', count($selectedSdgs));
            } else {
                $qb->join('t.sdgs', 's')
                   ->andWhere('s.id IN (:sdgs)')
                   ->setParameter('sdgs', $selectedSdgs);
            }
        }

        $paginator = new Paginator($qb);
        $totalCount = count($paginator);
        $totalPages = max(1, ceil($totalCount / $limit));

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $theses = $qb->getQuery()->getResult();

        // Fetches ONLY the SDGs that are toggled on in the database for the Library filter
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
            'active_sdgs' => $activeSdgs,
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