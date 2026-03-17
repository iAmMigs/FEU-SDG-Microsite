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
            ->leftJoin('t.sdgs', 's')
            ->addSelect('s')
            ->orderBy('t.createdAt', 'DESC');

        if (!empty($selectedGoals)) {
            if ($isExclusive) {
                /* * EXACT MATCH LOGIC (Fixed for Collection Hydration):
                 * 1. The SIZE() function natively checks that the thesis has the exact 
                 * amount of tags as the user's selection, bypassing the need for GROUP BY.
                 */
                $qb->andWhere('SIZE(t.sdgs) = :goalCount')
                   ->setParameter('goalCount', count($selectedGoals));

                /* * 2. Subquery ensures NO tags exist on this thesis that fall 
                 * outside of the user's selected goals.
                 */
                $qb->andWhere(
                    $qb->expr()->not(
                        $qb->expr()->exists(
                            "SELECT 1 FROM App\Entity\Thesis t2 
                             JOIN t2.sdgs s2 
                             WHERE t2.id = t.id AND s2.id NOT IN (:goals)"
                        )
                    )
                )->setParameter('goals', $selectedGoals);
                
            } else {
                /* * INCLUSIVE MATCH LOGIC:
                 * Subquery ensures we find any thesis connected to at least one selected goal,
                 * without restricting the main fetch array so all tags still display.
                 */
                $qb->andWhere(
                    $qb->expr()->exists(
                        "SELECT 1 FROM App\Entity\Thesis t3 
                         JOIN t3.sdgs s3 
                         WHERE t3.id = t.id AND s3.id IN (:goals)"
                    )
                )->setParameter('goals', $selectedGoals);
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

        $paginator = new Paginator($qb, true);
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