<?php

namespace App\Controller\Admin;

use App\Controller\Admin\ActivityCrudController;
use App\Controller\Admin\SdgCrudController;
use App\Controller\Admin\ThesisCrudController;
use App\Entity\Activity;
use App\Entity\Thesis;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    // 1. Inject the EntityManager to talk to the database
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function index(): Response
    {
        // 2. Count the total records in your tables
        $thesesCount = $this->entityManager->getRepository(Thesis::class)->count([]);
        $activitiesCount = $this->entityManager->getRepository(Activity::class)->count([]);

        // 3. Render a custom template and pass the numbers to it!
        return $this->render('admin-microsite/dashboard.html.twig', [
            'theses_count' => $thesesCount,
            'activities_count' => $activitiesCount,
        ]);
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