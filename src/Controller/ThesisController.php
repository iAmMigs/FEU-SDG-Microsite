<?php

namespace App\Controller;

use App\Entity\Thesis;
use App\Repository\ThesisRepository;
use App\Repository\SdgRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the Project Library search, filtering, and view counter logic.
 */
final class ThesisController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(Request $request, ThesisRepository $thesisRepository, SdgRepository $sdgRepository): Response
    {
        $searchAuthor = $request->query->get('author', '');
        $searchTitle = $request->query->get('title', '');
        $searchYear = $request->query->get('year', '');
        $selectedSdgs = $request->query->all('goals');
        $isExclusive = $request->query->getBoolean('exclusive', false);
        
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        /**
         * leftJoin and addSelect pre-fetches all associated SDGs in the initial query.
         */
        $qb = $thesisRepository->createQueryBuilder('t')
            ->leftJoin('t.sdgs', 'all_sdgs')
            ->addSelect('all_sdgs')
            ->where('t.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('t.createdAt', 'DESC');

        if ($searchAuthor) {
            $qb->andWhere('t.authors LIKE :author')
               ->setParameter('author', '%' . $searchAuthor . '%');
        }
        if ($searchTitle) {
            $qb->andWhere('t.title LIKE :title')
               ->setParameter('title', '%' . $searchTitle . '%');
        }
        if ($searchYear) {
            $qb->andWhere('t.researchYear = :searchYear')
               ->setParameter('searchYear', (int) $searchYear);
        }

        if (!empty($selectedSdgs)) {
            if ($isExclusive) {
                // Precision search: Must have EXACTLY these SDGs and NO OTHERS
                $qb->andWhere('t.id IN (
                    SELECT t_sub.id FROM App\Entity\Thesis t_sub
                    JOIN t_sub.sdgs s_sub
                    WHERE s_sub.id IN (:sdgs)
                    GROUP BY t_sub.id
                    HAVING COUNT(s_sub.id) = :count AND SIZE(t_sub.sdgs) = :count
                )')
                ->setParameter('sdgs', $selectedSdgs)
                ->setParameter('count', count($selectedSdgs));
            } else {
                // Standard search: Must have AT LEAST ONE of these SDGs
                $qb->andWhere('EXISTS (
                    SELECT 1 FROM App\Entity\Sdg s_sub2 
                    JOIN s_sub2.theses t_sub2 
                    WHERE t_sub2.id = t.id AND s_sub2.id IN (:sdgs)
                )')
                ->setParameter('sdgs', $selectedSdgs);
            }
        }

        $paginator = new Paginator($qb, true);
        $totalCount = count($paginator);
        $totalPages = max(1, ceil($totalCount / $limit));

        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $theses = iterator_to_array($paginator);
        $allSdgs = $sdgRepository->findBy([], ['id' => 'ASC']);

        return $this->render('SDG-Microsite/library.html.twig', [
            'theses' => $theses,
            'search_author' => $searchAuthor,
            'search_title' => $searchTitle,
            'selected_year' => $searchYear,
            'selected_goals' => $selectedSdgs,
            'is_exclusive' => $isExclusive,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'all_sdgs' => $allSdgs,
        ]);
    }

    #[Route('/library/thesis/{id}', name: 'app_library_show')]
    public function show(Request $request, Thesis $thesis, EntityManagerInterface $em): Response
    {
        if (!$thesis->isActive()) {
            throw $this->createNotFoundException('This thesis is no longer available.');
        }

        /**
         * Session-based view counter logic.
         * Prevents manipulation by recording accessed project IDs in the active user session.
         */
        $session = $request->getSession();
        $viewedTheses = $session->get('viewed_theses', []);

        if (!in_array($thesis->getId(), $viewedTheses)) {
            $thesis->incrementViews();
            $em->flush();

            $viewedTheses[] = $thesis->getId();
            $session->set('viewed_theses', $viewedTheses);
        }

        return $this->render('SDG-Microsite/project_view.html.twig', [
            'thesis' => $thesis,
        ]);
    }
}