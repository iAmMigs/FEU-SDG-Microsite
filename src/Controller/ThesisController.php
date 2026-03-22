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

        // ONLY FETCH ACTIVE THESES
        $qb = $thesisRepository->createQueryBuilder('t')
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
        if ($searchKeyword) {
            $qb->andWhere('t.description LIKE :keyword OR t.title LIKE :keyword OR t.authors LIKE :keyword')
               ->setParameter('keyword', '%' . $searchKeyword . '%');
        }

        if (!empty($selectedSdgs)) {
            if ($isExclusive) {
                foreach ($selectedSdgs as $index => $sdgId) {
                    $alias = 's' . $index;
                    $qb->join('t.sdgs', $alias)
                       ->andWhere($alias . '.id = :sdg' . $index)
                       ->setParameter('sdg' . $index, $sdgId);
                }
                $qb->andWhere('SIZE(t.sdgs) = :count')
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

    #[Route('/library/thesis/{id}', name: 'app_library_show')]
    public function show(Thesis $thesis, EntityManagerInterface $em): Response
    {
        // Block direct URL access if the thesis is hidden
        if (!$thesis->isActive()) {
            throw $this->createNotFoundException('This thesis is no longer available.');
        }

        // Increment the view counter directly
        $thesis->incrementViews();
        $em->flush();

        return $this->render('SDG-Microsite/library/show.html.twig', [
            'thesis' => $thesis,
        ]);
    }
}