<?php

namespace App\Controller\Admin;

use App\Repository\ActivityRepository;
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
        private ActivityRepository $activityRepository
    ) {
    }

    public function index(): Response
    {
        return $this->render('Admin-Microsite/dashboard.html.twig', [
            'theses_count' => $this->thesisRepository->count([]),
            'activities_count' => $this->activityRepository->count([]),
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
        // THE FIX: Removed the "assets/" prefix so AssetMapper can correctly locate it.
        return Assets::new()
            ->addCssFile('styles/admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard Overview', 'fa fa-chart-pie');

        yield MenuItem::section('Data Management');
        yield MenuItem::linkTo(ThesisCrudController::class, 'Theses & Studies', 'fas fa-book-bookmark');
        yield MenuItem::linkTo(SdgCrudController::class, 'SDG Categories', 'fas fa-bullseye');
        yield MenuItem::linkTo(ActivityCrudController::class, 'Activities & Events', 'fas fa-newspaper');

        yield MenuItem::section('Audiit Logs');

        yield MenuItem::section('Public Portal');
        yield MenuItem::linkToRoute('View Site', 'fas fa-arrow-right-from-bracket', 'app_home');
    }
}