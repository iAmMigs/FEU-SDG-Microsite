<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use App\Repository\SdgRepository;
use App\Repository\ThesisRepository;
use App\Repository\LeadingVoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ActivityRepository $activityRepository, ThesisRepository $thesisRepository, SdgRepository $sdgRepository, LeadingVoiceRepository $leadingVoiceRepository): Response
    {
        $latestActivities = $activityRepository->findBy([], ['eventDate' => 'DESC'], 3);
        $totalTheses = $thesisRepository->count([]);
        
        // CRITICAL FIX: Only fetch the active SDGs to pass their IDs to the template
        $activeSdgs = $sdgRepository->findBy(['isActive' => true]);
        $activeSdgIds = array_map(fn($sdg) => $sdg->getId(), $activeSdgs);
        $leadingVoices = $leadingVoiceRepository->findBy([], null, 4);

        return $this->render('SDG-Microsite/home/index.html.twig', [
            'latest_activities' => $latestActivities,
            'total_theses' => $totalTheses,
            'active_sdg_ids' => $activeSdgIds,
            'all_sdgs' => $this->getAllSdgsData(), 
            'leading_voices' => $leadingVoices,
        ]);
    }

    private function getAllSdgsData(): array
    {
        return [
            ['num' => 1, 'title' => 'No Poverty', 'desc' => 'End poverty in all its forms everywhere.'],
            ['num' => 2, 'title' => 'Zero Hunger', 'desc' => 'End hunger, achieve food security and improved nutrition and promote sustainable agriculture.'],
            ['num' => 3, 'title' => 'Good Health and Well-Being', 'desc' => 'Ensuring healthy lives and promoting well-being for all at all ages is essential to sustainable development.'],
            ['num' => 4, 'title' => 'Quality Education', 'desc' => 'Obtaining a quality education is the foundation to improving people’s lives.'],
            ['num' => 5, 'title' => 'Gender Equality', 'desc' => 'Achieve gender equality and empower all women and girls.'],
            ['num' => 6, 'title' => 'Clean Water and Sanitation', 'desc' => 'Ensure availability and sustainable management of water and sanitation for all.'],
            ['num' => 7, 'title' => 'Affordable and Clean Energy', 'desc' => 'Energy is central to nearly every major challenge and opportunity.'],
            ['num' => 8, 'title' => 'Decent Work and Economic Growth', 'desc' => 'Sustainable economic growth requires societies to create the conditions that allow people to have quality jobs.'],
            ['num' => 9, 'title' => 'Industry, Innovation and Infrastructure', 'desc' => 'Investments in infrastructure are crucial to achieving sustainable development.'],
            ['num' => 10, 'title' => 'Reduced Inequalities', 'desc' => 'Reduce inequality within and among countries.'],
            ['num' => 11, 'title' => 'Sustainable Cities and Communities', 'desc' => 'Making cities safe and sustainable means ensuring access to safe and affordable housing, and upgrading slum settlements.'],
            ['num' => 12, 'title' => 'Responsible Consumption and Production', 'desc' => 'Sustainable consumption and production is about promoting resource and energy efficiency.'],
            ['num' => 13, 'title' => 'Climate Action', 'desc' => 'Take urgent action to combat climate change and its impacts.'],
            ['num' => 14, 'title' => 'Life Below Water', 'desc' => 'Conserve and sustainably use the oceans, seas and marine resources for sustainable development.'],
            ['num' => 15, 'title' => 'Life on Land', 'desc' => 'Protect, restore and promote sustainable use of terrestrial ecosystems, sustainably manage forests, combat desertification, and halt and reverse land degradation and halt biodiversity loss.'],
            ['num' => 16, 'title' => 'Peace, Justice and Strong Institutions', 'desc' => 'Promote peaceful and inclusive societies for sustainable development, provide access to justice for all and build effective, accountable and inclusive institutions at all levels.'],
            ['num' => 17, 'title' => 'Partnerships for the Goals', 'desc' => 'A successful sustainable development agenda requires partnerships between governments, the private sector and civil society.']
        ];
    }
}