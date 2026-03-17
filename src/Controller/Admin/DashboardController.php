<?php

namespace App\Controller\Admin;

use App\Controller\Admin\ActivityCrudController;
use App\Controller\Admin\SdgCrudController;
use App\Controller\Admin\ThesisCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        
        return $this->redirect(
            $adminUrlGenerator->setController(ThesisCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/Tech_Logo.png" class="h-8 w-auto inline-block mr-2" alt="FEU Logo"> SDG Admin')
            ->disableDarkMode();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Library Management');
        yield MenuItem::linkTo(ThesisCrudController::class, 'Theses & Projects', 'fas fa-book');
        yield MenuItem::linkTo(SdgCrudController::class, 'SDG Categories', 'fas fa-globe');
        
        yield MenuItem::section('Content Management');
        yield MenuItem::linkTo(ActivityCrudController::class, 'News & Activities', 'fas fa-newspaper');
        
        yield MenuItem::section('Public Site');
        yield MenuItem::linkToRoute('View Website', 'fas fa-eye', 'app_home');
    }
}