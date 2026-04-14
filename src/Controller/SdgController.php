<?php

namespace App\Controller;

use App\Repository\SdgRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the display of the Sustainable Development Goals directory.
 */
final class SdgController extends AbstractController
{
    #[Route('/sdgs', name: 'app_sdgs')]
    public function index(SdgRepository $sdgRepository): Response
    {
        $activeSdgs = $sdgRepository->findBy(['isActive' => true]);
        $activeSdgIds = array_map(fn($sdg) => $sdg->getId(), $activeSdgs);

        return $this->render('SDG-Microsite/sdg/index.html.twig', [
            'active_sdg_ids' => $activeSdgIds,
            'all_sdgs' => $this->getAllSdgsData(), 
        ]);
    }

    /**
     * Provides comprehensive static data definitions for all 17 Sustainable Development Goals.
     */
    private function getAllSdgsData(): array
    {
        return [
            [
                'num' => 1, 
                'title' => 'No Poverty', 
                'desc' => 'End poverty in all its forms everywhere.',
                'long_desc' => 'Eradicating poverty in all its forms remains one of the greatest challenges facing humanity. This goal targets the most vulnerable, aims to increase access to basic resources and services, and supports communities affected by conflict and climate-related disasters.'
            ],
            [
                'num' => 2, 
                'title' => 'Zero Hunger', 
                'desc' => 'End hunger, achieve food security and improved nutrition and promote sustainable agriculture.',
                'long_desc' => 'Aims to end hunger and all forms of malnutrition by ensuring all people have access to sufficient and nutritious food all year. This requires promoting sustainable agricultural practices, improving the livelihoods and capacities of small-scale farmers, and allowing equal access to land, technology, and markets.'
            ],
            [
                'num' => 3, 
                'title' => 'Good Health and Well-Being', 
                'desc' => 'Ensuring healthy lives and promoting well-being for all at all ages is essential to sustainable development.',
                'long_desc' => 'Focuses on ensuring healthy lives and promoting well-being across all ages. This includes taking major strides in increasing life expectancy, reducing maternal and child mortality, fighting communicable diseases, and achieving universal health coverage and access to safe and effective medicines and vaccines.'
            ],
            [
                'num' => 4, 
                'title' => 'Quality Education', 
                'desc' => 'Obtaining a quality education is the foundation to improving people’s lives.',
                'long_desc' => 'Aims to provide inclusive and equitable quality education and promote lifelong learning opportunities for all. It emphasizes the need for access to universal pre-primary, primary, and secondary education, as well as affordable technical, vocational, and higher education.'
            ],
            [
                'num' => 5, 
                'title' => 'Gender Equality', 
                'desc' => 'Achieve gender equality and empower all women and girls.',
                'long_desc' => 'Focuses on ending all forms of discrimination and violence against women and girls everywhere. It champions equal rights to economic resources, access to ownership and control over land, and full and effective participation and equal opportunities for leadership at all levels of decision-making.'
            ],
            [
                'num' => 6, 
                'title' => 'Clean Water and Sanitation', 
                'desc' => 'Ensure availability and sustainable management of water and sanitation for all.',
                'long_desc' => 'Calls for universal and equitable access to safe and affordable drinking water, as well as adequate and equitable sanitation and hygiene for all. It emphasizes protecting and restoring water-related ecosystems and supporting local community participation in improving water and sanitation management.'
            ],
            [
                'num' => 7, 
                'title' => 'Affordable and Clean Energy', 
                'desc' => 'Energy is central to nearly every major challenge and opportunity.',
                'long_desc' => 'Aims to ensure access to affordable, reliable, sustainable, and modern energy for all. This requires expanding infrastructure and upgrading technology to provide clean energy sources, thereby encouraging growth and helping the environment.'
            ],
            [
                'num' => 8, 
                'title' => 'Decent Work and Economic Growth', 
                'desc' => 'Sustainable economic growth requires societies to create the conditions that allow people to have quality jobs.',
                'long_desc' => 'Promotes sustained, inclusive, and sustainable economic growth, full and productive employment, and decent work for all. It targets eradicating forced labor, human trafficking, and child labor, while fostering safe and secure working environments.'
            ],
            [
                'num' => 9, 
                'title' => 'Industry, Innovation and Infrastructure', 
                'desc' => 'Investments in infrastructure are crucial to achieving sustainable development.',
                'long_desc' => 'Seeks to build resilient infrastructure, promote inclusive and sustainable industrialization, and foster innovation. It highlights the importance of technological progress in finding lasting solutions to both economic and environmental challenges.'
            ],
            [
                'num' => 10, 
                'title' => 'Reduced Inequalities', 
                'desc' => 'Reduce inequality within and among countries.',
                'long_desc' => 'Focuses on reducing inequalities in income as well as those based on age, sex, disability, race, ethnicity, origin, religion, or economic or other status. It advocates for the social, economic, and political inclusion of all.'
            ],
            [
                'num' => 11, 
                'title' => 'Sustainable Cities and Communities', 
                'desc' => 'Making cities safe and sustainable means ensuring access to safe and affordable housing, and upgrading slum settlements.',
                'long_desc' => 'Aims to make cities and human settlements inclusive, safe, resilient, and sustainable. This involves providing access to safe, affordable, accessible, and sustainable transport systems, creating green public spaces, and improving urban planning and management.'
            ],
            [
                'num' => 12, 
                'title' => 'Responsible Consumption and Production', 
                'desc' => 'Sustainable consumption and production is about promoting resource and energy efficiency.',
                'long_desc' => 'Focuses on ensuring sustainable consumption and production patterns. It urges businesses, consumers, and policymakers to adopt sustainable practices, reduce waste generation through prevention, reduction, recycling, and reuse, and sustainably manage natural resources.'
            ],
            [
                'num' => 13, 
                'title' => 'Climate Action', 
                'desc' => 'Take urgent action to combat climate change and its impacts.',
                'long_desc' => 'Calls for urgent action to combat climate change and its impacts by regulating emissions and promoting developments in renewable energy. It emphasizes integrating disaster risk measures and sustainable natural resource management into national development strategies.'
            ],
            [
                'num' => 14, 
                'title' => 'Life Below Water', 
                'desc' => 'Conserve and sustainably use the oceans, seas and marine resources for sustainable development.',
                'long_desc' => 'Dedicated to the conservation and sustainable use of the oceans, seas, and marine resources. It targets the prevention and significant reduction of marine pollution of all kinds, and the sustainable management and protection of marine and coastal ecosystems.'
            ],
            [
                'num' => 15, 
                'title' => 'Life on Land', 
                'desc' => 'Protect, restore and promote sustainable use of terrestrial ecosystems, sustainably manage forests, combat desertification, and halt and reverse land degradation and halt biodiversity loss.',
                'long_desc' => 'Aims to protect, restore, and promote the sustainable use of terrestrial ecosystems. It focuses on sustainably managing forests, combating desertification, reversing land degradation, and halting biodiversity loss to ensure the survival of diverse species.'
            ],
            [
                'num' => 16, 
                'title' => 'Peace, Justice and Strong Institutions', 
                'desc' => 'Promote peaceful and inclusive societies for sustainable development, provide access to justice for all and build effective, accountable and inclusive institutions at all levels.',
                'long_desc' => 'Promotes peaceful and inclusive societies for sustainable development, provides access to justice for all, and builds effective, accountable, and inclusive institutions. It focuses on significantly reducing all forms of violence and ending abuse, exploitation, and trafficking.'
            ],
            [
                'num' => 17, 
                'title' => 'Partnerships for the Goals', 
                'desc' => 'A successful sustainable development agenda requires partnerships between governments, the private sector and civil society.',
                'long_desc' => 'Focuses on strengthening the means of implementation and revitalizing the global partnership for sustainable development. It emphasizes that a successful sustainable development agenda requires inclusive partnerships built upon principles and values, a shared vision, and shared goals.'
            ]
        ];
    }
}