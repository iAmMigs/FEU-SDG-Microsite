<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Sdg;
use App\Entity\Thesis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Define ALL 17 SDGs
        $sdgData = [
            1 => 'No Poverty',
            2 => 'Zero Hunger',
            3 => 'Good Health and Well-Being',
            4 => 'Quality Education',
            5 => 'Gender Equality',
            6 => 'Clean Water and Sanitation',
            7 => 'Affordable and Clean Energy',
            8 => 'Decent Work and Economic Growth',
            9 => 'Industry, Innovation and Infrastructure',
            10 => 'Reduced Inequalities',
            11 => 'Sustainable Cities and Communities',
            12 => 'Responsible Consumption and Production',
            13 => 'Climate Action',
            14 => 'Life Below Water',
            15 => 'Life on Land',
            16 => 'Peace, Justice and Strong Institutions',
            17 => 'Partnerships for the Goals',
        ];

        // Define which SDGs are toggled "ON" by default
        $activeSdgIds = [3, 4, 7, 8, 9, 11, 12, 17];

        $sdgEntities = [];
        foreach ($sdgData as $id => $name) {
            $sdg = new Sdg();
            $sdg->setId($id);
            $sdg->setName($name);
            $sdg->setIsActive(in_array($id, $activeSdgIds));
            
            $manager->persist($sdg);
            $sdgEntities[$id] = $sdg;
        }

        // We only use active SDGs to attach to our dummy data
        $sdgs = array_values(array_filter($sdgEntities, fn($s) => $s->isActive()));

        // Native PHP Dummy Data arrays
        $dummyTitles = ['Sustainable Urban Development', 'AI in Agriculture', 'Renewable Energy Systems', 'Water Purification Models', 'Smart City Traffic Optimization', 'Waste Management Automation'];
        $dummyLocations = ['Metro Manila', 'Cebu', 'Davao', 'Rural Philippines', 'Coastal Communities'];
        $dummyAuthors = ['Juan Dela Cruz', 'Maria Santos', 'Jose Rizal', 'Ana Reyes', 'Pedro Penduko'];
        $dummyDescriptions = [
            "This study explores various methodologies for improving local infrastructure while maintaining ecological balance. The findings suggest a strong correlation between community engagement and long-term sustainability.",
            "An in-depth analysis of current systems utilizing machine learning to optimize resource allocation. We propose a novel algorithm that reduces waste by 15% in simulated environments.",
            "By implementing low-cost, open-source hardware, this research demonstrates a scalable solution for off-grid communities. Extensive field testing validates the durability of the proposed prototypes.",
            "A comprehensive review of existing policies and their impact on technological adoption. The paper argues for a revised framework that incentivizes green innovation in the private sector."
        ];

        // 2. Create Theses (Library)
        for ($i = 0; $i < 20; $i++) {
            $thesis = new Thesis();
            
            // Generate Random Content natively
            $title = $dummyTitles[array_rand($dummyTitles)] . ' in the context of ' . $dummyLocations[array_rand($dummyLocations)];
            $authorStr = $dummyAuthors[array_rand($dummyAuthors)] . ', ' . $dummyAuthors[array_rand($dummyAuthors)];
            $desc = $dummyDescriptions[array_rand($dummyDescriptions)];
            
            $thesis->setTitle($title);
            $thesis->setDescription($desc);
            $thesis->setAuthors($authorStr);
            
            // Explicitly ensure the cover image is NULL
            $thesis->setCoverImage(null);

            // Pick 1 to 3 random SDGs
            $numSdgs = rand(1, 3);
            $randomSdgKeys = (array) array_rand($sdgs, $numSdgs);
            foreach ($randomSdgKeys as $key) {
                $thesis->addSdg($sdgs[$key]);
            }

            $thesis->setViews(rand(0, 1500));
            
            // Generate a random date within the last year
            $randomTimestamp = mt_rand(strtotime('-1 year'), time());
            $randomDate = new \DateTimeImmutable('@' . $randomTimestamp);
            $thesis->setCreatedAt($randomDate);
            
            // Randomly set ~85% of theses as active
            $thesis->setIsActive(rand(1, 100) <= 85);

            $manager->persist($thesis);
        }

        // 3. Create Specific Activities (News/Events)
        $activitiesData = [
            [
                'title' => 'Tech Summit 2025: Innovating for a Sustainable Future',
                'category' => 'Seminar',
                'content' => "<p>FEU Tech recently hosted the annual Tech Summit, gathering industry leaders, students, and faculty to discuss the intersection of technology and sustainability.</p><p>Keynotes focused on AI-driven energy management, sustainable infrastructure, and the role of academic institutions in achieving global goals.</p>",
                'date' => '2025-05-15',
                'goals' => [9, 11, 4],
                'isActive' => true
            ],
            [
                'title' => 'Community Outreach: Project Clean Water Initiative',
                'category' => 'Community',
                'content' => "<p>Student volunteers from the Civil Engineering department partnered with local NGOs to install low-cost water filtration systems in underserved communities.</p><p>This initiative not only provides clean drinking water but also educates locals on proper sanitation and system maintenance.</p>",
                'date' => '2025-04-22',
                'goals' => [6, 3, 17],
                'isActive' => true
            ],
            [
                'title' => 'Student Research Highlights: Renewable Energy Prototypes',
                'category' => 'Research',
                'content' => "<p>A team of graduating Electrical Engineering students successfully tested their prototype for a high-efficiency solar inverter.</p><p>Their research aims to make solar energy more affordable and accessible for residential use in the Philippines.</p>",
                'date' => '2025-06-10',
                'goals' => [7, 13, 9],
                'isActive' => true
            ],
            [
                'title' => 'Green Campus: FEU Commits to Zero Waste by 2030',
                'category' => 'Initiative',
                'content' => "<p>FEU has officially announced its comprehensive 'Zero Waste 2030' policy. The initiative includes a campus-wide ban on single-use plastics, enhanced recycling programs, and on-site composting facilities.</p>",
                'date' => '2025-01-10',
                'goals' => [12, 11, 13],
                'isActive' => true
            ],
            [
                'title' => 'Women in STEM: Breaking Barriers, Building the Future',
                'category' => 'Seminar',
                'content' => "<p>A panel discussion featuring prominent women engineers and technologists. The event highlighted the importance of gender equality in technical fields and provided mentorship opportunities for female students.</p>",
                'date' => '2025-03-08',
                'goals' => [5, 4, 10],
                'isActive' => true
            ],
            [
                'title' => 'Smart City Urban Planning Hackathon',
                'category' => 'Event',
                'content' => "<p>Students from various disciplines collaborated in a 48-hour hackathon to design tech-driven solutions for urban challenges in Metro Manila, focusing on traffic optimization and disaster resilience.</p>",
                'date' => '2025-08-14',
                'goals' => [11, 9, 13],
                'isActive' => true
            ],
            [
                'title' => 'Bridging the Digital Divide: Tech Literacy in Rural Schools',
                'category' => 'Community',
                'content' => "<p>The IT department organized a weekend boot camp in rural provinces, teaching basic coding and computer literacy to elementary students to promote quality education for all.</p>",
                'date' => '2025-07-20',
                'goals' => [4, 10, 1],
                'isActive' => true
            ],
            [
                'title' => 'Sustainable Agriculture: AI for Crop Monitoring',
                'category' => 'Research',
                'content' => "<p>Computer Science students presented their thesis on using drone imagery and AI to help local farmers detect crop diseases early, contributing to food security.</p>",
                'date' => '2025-09-05',
                'goals' => [2, 12, 15],
                'isActive' => true
            ],
            [
                'title' => 'Tech for Good: Partnership with the UN Development Programme',
                'category' => 'Initiative',
                'content' => "<p>FEU Tech has formally partnered with the UNDP to align its engineering curriculum with the Sustainable Development Goals, ensuring graduates are equipped to tackle global challenges.</p>",
                'date' => '2025-10-28',
                'goals' => [8, 9, 17],
                'isActive' => true
            ],
            [
                'title' => 'Hustlers: Student Entrepreneurship Program',
                'category' => 'Workshop',
                'content' => "<p>Creates opportunities for students to test their ideas, receive customer feedback, and build confidence as business owners.</p><p>Through Hustlers, FEU Tech nurtures student interest in sustainability, innovation, and impact-driven careers.</p>",
                'date' => '2024-12-02',
                'goals' => [4, 8, 12],
                'isActive' => true
            ]
        ];

        foreach ($activitiesData as $data) {
            $activity = new Activity();
            $activity->setTitle($data['title'])
                     ->setCategory($data['category'])
                     ->setContent($data['content'])
                     ->setEventDate(new \DateTime($data['date']))
                     ->setCreatedAt(new \DateTimeImmutable($data['date']))
                     ->setIsActive($data['isActive'])
                     ->setPublishAt(new \DateTime($data['date']));

            foreach ($data['goals'] as $goalNum) {
                if (isset($sdgEntities[$goalNum])) {
                    $activity->addSdg($sdgEntities[$goalNum]);
                }
            }

            $manager->persist($activity);
        }

        $manager->flush();
    }
}