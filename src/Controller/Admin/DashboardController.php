<?php

namespace App\Controller\Admin;

// 1. Swap Entity imports for Crud Controller imports
use App\Controller\Admin\ActivityCrudController;
use App\Controller\Admin\SdgGoalCrudController;
use App\Controller\Admin\ThesisCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator; 
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        return $this->redirect(
            $adminUrlGenerator->setController(ActivityCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/Tech_Logo.png" class="h-8 w-auto inline-block mr-2" alt="FEU Logo"> SDG Admin')
            ->disableDarkMode()
            ->renderContentMaximized();
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('styles/app.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Library Management');
        // 2. Use linkTo() instead of linkToCrud()
        yield MenuItem::linkTo(ThesisCrudController::class, 'Theses & Projects', 'fas fa-book');
        yield MenuItem::linkTo(SdgGoalCrudController::class, 'SDG Categories', 'fas fa-globe');
        
        yield MenuItem::section('Content Management');
        yield MenuItem::linkTo(ActivityCrudController::class, 'News & Activities', 'fas fa-newspaper');
        
        yield MenuItem::section('Public Site');
        yield MenuItem::linkToRoute('View Microsite', 'fas fa-eye', 'app_home');
    }
}