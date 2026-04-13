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
        private SdgRepository $sdgRepository // Injected SDG Repository for the dropdown filter
    ) {
    }

    public function index(): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        // --- CHART 1: Theses submitted over the past 6 months ---
        $monthsLabels = [];
        $monthsDataMap = [];
        
        // Generate the last 6 months list (e.g., "Oct 2025", "Nov 2025", etc.)
        for ($i = 5; $i >= 0; $i--) {
            $date = (new \DateTime())->modify("-$i months");
            $label = $date->format('M Y');
            $monthsLabels[] = $label;
            $monthsDataMap[$label] = 0;
        }

        // Fetch theses from the last 6 months
        $sixMonthsAgo = (new \DateTimeImmutable())->modify('-5 months')->modify('first day of this month');
        $recentTheses = $this->thesisRepository->createQueryBuilder('t')
            ->where('t.createdAt >= :date')
            ->setParameter('date', $sixMonthsAgo)
            ->getQuery()->getResult();

        // Count theses per month
        foreach ($recentTheses as $thesis) {
            $label = $thesis->getCreatedAt()->format('M Y');
            if (isset($monthsDataMap[$label])) {
                $monthsDataMap[$label]++;
            }
        }

        // --- CHART 2: Top 10 Most Viewed Theses (with SDG Filter) ---
        $selectedSdg = $request->query->get('sdg');

        $qb = $this->thesisRepository->createQueryBuilder('t')
            ->orderBy('t.views', 'DESC')
            ->setMaxResults(10);

        // Apply filter if an SDG is selected
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
            // Truncate long titles so they don't break the chart layout
            $topLabels[] = (mb_strlen($title) > 30) ? mb_substr($title, 0, 30) . '...' : $title;
            $topData[] = $t->getViews();
        }

        return $this->render('Admin-Microsite/dashboard.html.twig', [
            'theses_count' => $this->thesisRepository->count([]),
            'activities_count' => $this->activityRepository->count([]),
            
            // Chart 1 Variables
            'chart_months_labels' => json_encode($monthsLabels),
            'chart_months_data' => json_encode(array_values($monthsDataMap)),
            
            // Chart 2 Variables
            'chart_top_labels' => json_encode($topLabels),
            'chart_top_data' => json_encode($topData),
            
            // Filter Dropdown Variables
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
        // The leading slash bypasses AssetMapper and looks directly in the public/ folder
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