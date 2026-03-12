<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Activity;
use App\Entity\SdgGoal;
use App\Entity\Thesis;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // By default, EasyAdmin shows a blank page. 
        // We can just render the default template for now.
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('FEU SDG Microsite Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Library Management');
        // Notice the capital T and C in linkToCrud
        yield MenuItem::linkToCrud('Theses & Projects', 'fas fa-book', Thesis::class);
        yield MenuItem::linkToCrud('SDG Categories', 'fas fa-globe', SdgGoal::class);
        
        yield MenuItem::section('Content Management');
        yield MenuItem::linkToCrud('News & Activities', 'fas fa-newspaper', Activity::class);
        
        yield MenuItem::section('Public Site');
        yield MenuItem::linkToRoute('View Microsite', 'fas fa-eye', 'app_home');
    }
}