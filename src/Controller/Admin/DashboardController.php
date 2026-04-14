<?php

namespace App\Controller\Admin;

use App\Repository\ActivityRepository;
use App\Repository\SdgRepository;
use App\Repository\ThesisRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Main administration dashboard controller.
 * Configures the global layout, assets, sidebar navigation, and dashboard metrics.
 */
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ThesisRepository $thesisRepository,
        private ActivityRepository $activityRepository,
        private SdgRepository $sdgRepository 
    ) {
    }

    public function index(): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $monthsLabels = [];
        $monthsDataMap = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = (new \DateTime())->modify("-$i months");
            $label = $date->format('M Y');
            $monthsLabels[] = $label;
            $monthsDataMap[$label] = 0;
        }

        $sixMonthsAgo = (new \DateTimeImmutable())->modify('-5 months')->modify('first day of this month');
        
        /**
         * Optimizes dashboard load times by strictly fetching the 'createdAt' date property
         * as a lightweight array instead of hydrating full Thesis entity objects into memory.
         */
        $recentTheses = $this->thesisRepository->createQueryBuilder('t')
            ->select('t.createdAt')
            ->where('t.createdAt >= :date')
            ->setParameter('date', $sixMonthsAgo)
            ->getQuery()
            ->getArrayResult();

        foreach ($recentTheses as $thesis) {
            $label = $thesis['createdAt']->format('M Y');
            if (isset($monthsDataMap[$label])) {
                $monthsDataMap[$label]++;
            }
        }

        $selectedSdg = $request->query->get('sdg');

        $qb = $this->thesisRepository->createQueryBuilder('t')
            ->orderBy('t.views', 'DESC')
            ->setMaxResults(10);

        if ($selectedSdg) {
            $qb->join('t.sdgs', 's')
               ->andWhere('s.id = :sdg')
               ->setParameter('sdg', $selectedSdg);
        }

        $topTheses = $qb->getQuery()->getResult();

        $topLabels = [];
        $topData = [];
        foreach ($topTheses as $t) {
            $title = $t->getTitle();
            $topLabels[] = (mb_strlen($title) > 30) ? mb_substr($title, 0, 30) . '...' : $title;
            $topData[] = $t->getViews();
        }

        return $this->render('Admin-Microsite/dashboard.html.twig', [
            'theses_count' => $this->thesisRepository->count([]),
            'activities_count' => $this->activityRepository->count([]),
            
            'chart_months_labels' => json_encode($monthsLabels),
            'chart_months_data' => json_encode(array_values($monthsDataMap)),
            
            'chart_top_labels' => json_encode($topLabels),
            'chart_top_data' => json_encode($topData),
            
            'sdgs' => $this->sdgRepository->findBy([], ['id' => 'ASC']),
            'selected_sdg' => $selectedSdg
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<div class="flex items-center gap-2"><img src="/images/Tech_Logo.png" style="max-height: 28px;"><span style="font-family: \'Montserrat\', sans-serif; font-weight: 700; letter-spacing: -0.5px; color: #166534; font-size: 1.2rem; padding-top: 2px;"> Admin Console</span></div>')
            ->setFaviconPath('/images/Tech_Logo.png');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('/css/admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard Overview', 'fa fa-chart-pie');

        yield MenuItem::section('Content Management');
        yield MenuItem::linkTo(ThesisCrudController::class, 'Theses & Studies', 'fas fa-book-bookmark');
        yield MenuItem::linkTo(ActivityCrudController::class, 'Activities & Events', 'fas fa-newspaper');
        
        yield MenuItem::section('Data Management');
        yield MenuItem::linkTo(SdgCrudController::class, 'SDG Categories', 'fas fa-bullseye');
        yield MenuItem::linkTo(ActivityCategoryCrudController::class, 'Activity Categories', 'fas fa-tags');
        yield MenuItem::linkTo(LeadingVoiceCrudController::class, 'Featured Voices', 'fas fa-users');
        yield MenuItem::linkTo(ProjectTypeCrudController::class, 'Project Types', 'fas fa-tags');
        yield MenuItem::linkTo(CollegeCrudController::class, 'Colleges', 'fas fa-school');

        yield MenuItem::section('Public Portal');
        yield MenuItem::linkToRoute('View Site', 'fas fa-arrow-right-from-bracket', 'app_home');
    }
}