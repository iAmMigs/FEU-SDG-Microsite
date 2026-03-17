<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\SdgGoal;
use App\Entity\Thesis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Create the 8 official SDG Goals
        $sdgData = [
            3 => 'Good Health and Well-Being',
            4 => 'Quality Education',
            7 => 'Affordable and Clean Energy',
            8 => 'Decent Work and Economic Growth',
            9 => 'Industry, Innovation and Infrastructure',
            11 => 'Sustainable Cities and Communities',
            12 => 'Responsible Consumption and Production',
            17 => 'Partnerships for the Goals',
        ];

        $sdgEntities = [];
        foreach ($sdgData as $number => $name) {
            $sdg = new SdgGoal();
            $sdg->setGoalNumber($number);
            $sdg->setName($name);
            $manager->persist($sdg);
            $sdgEntities[$number] = $sdg;
        }

        // 2. Create Theses
        $thesesData = [
            [
                'title' => 'Incer-Eco Tech: Waste-to-Energy Incinerator',
                'description' => 'Incer-Eco Tech offers a small-scale waste-to-energy incinerator designed to provide a sustainable solution for rural areas facing challenges with waste management and electrical supply.',
                'authors' => 'Engr. Jose Florenz Somigao, Rome Arist Mendoza',
                'views' => 1405,
                'goals' => [7, 11, 12]
            ],
            [
                'title' => 'Impact of AI-Assisted Tutoring on Engineering Students',
                'description' => 'Analyzing the effectiveness of machine learning algorithms in creating personalized tutoring modules for freshman engineering students struggling with advanced calculus.',
                'authors' => 'Anna Reyes',
                'views' => 1204,
                'goals' => [4]
            ],
            [
                'title' => 'Solar-Powered Water Filtration for Rural Communities',
                'description' => 'This study explores the feasibility and efficiency of integrating solar panels with portable water filtration units to provide clean drinking water in off-grid rural communities.',
                'authors' => 'J. Dela Cruz',
                'views' => 892,
                'goals' => [3, 7]
            ],
            [
                'title' => 'Smart Traffic Management using Computer Vision',
                'description' => 'Developing an edge-computing solution that uses CCTV cameras and computer vision to optimize traffic light timings, reducing carbon emissions from idling vehicles.',
                'authors' => 'Maria Santos, K. Villanueva',
                'views' => 2100,
                'goals' => [9, 11]
            ],
            [
                'title' => 'Recycled Plastic as Aggregate in Concrete Mixtures',
                'description' => 'Testing the structural integrity and load-bearing capacity of concrete mixed with shredded PET bottles for affordable housing construction.',
                'authors' => 'Engr. L. Bautista',
                'views' => 1750,
                'goals' => [9, 11, 12]
            ],
            [
                'title' => 'Predictive Maintenance for Wind Turbines',
                'description' => 'Using IoT sensors and predictive machine learning models to identify acoustic anomalies in wind turbine blades before structural failure occurs.',
                'authors' => 'P. Fernandez',
                'views' => 640,
                'goals' => [7, 9]
            ],
            [
                'title' => 'Urban Farming: Automated Aquaponics System',
                'description' => 'Designing an automated, low-cost aquaponics system for urban apartments, utilizing Arduino controllers to monitor pH and nutrient levels.',
                'authors' => 'R. Garcia',
                'views' => 310,
                'goals' => [3, 11, 12]
            ],
            [
                'title' => 'Blockchain for Transparent Supply Chains',
                'description' => 'Implementing a hyperledger blockchain framework to track the sourcing of raw materials for local textile industries to ensure ethical labor practices.',
                'authors' => 'S. Lim, T. Tan',
                'views' => 950,
                'goals' => [8, 12]
            ],
            [
                'title' => 'Telemedicine Platform for Remote Barangays',
                'description' => 'A low-bandwidth mobile application designed to connect rural healthcare workers with specialized doctors in Metro Manila for remote consultations.',
                'authors' => 'Dr. C. Ocampo, Anna Reyes',
                'views' => 1820,
                'goals' => [3, 11]
            ],
            [
                'title' => 'Gamification of STEM Education for Grade Schoolers',
                'description' => 'Developing an interactive mobile game that teaches basic physics and programming concepts to public school students in the Philippines.',
                'authors' => 'M. Torres',
                'views' => 420,
                'goals' => [4]
            ],
            [
                'title' => 'Optimization of Jeepney Routes via Genetic Algorithms',
                'description' => 'A computational study mapping optimal public transportation routes in Metro Manila to reduce fuel consumption and passenger wait times.',
                'authors' => 'J. Dela Cruz',
                'views' => 1150,
                'goals' => [11]
            ],
            [
                'title' => 'Flood Prediction using River Sensors',
                'description' => 'Deploying cost-effective ultrasonic water level sensors across the Marikina river basin hooked into a real-time early warning SMS system.',
                'authors' => 'Engr. Jose Florenz Somigao',
                'views' => 2500,
                'goals' => [9, 11]
            ],
            [
                'title' => 'Biodegradable Packaging from Banana Pseudostem',
                'description' => 'Extracting cellulose from agricultural waste (banana trees) to create a viable, biodegradable alternative to single-use plastic packaging.',
                'authors' => 'A. Ramos',
                'views' => 880,
                'goals' => [12]
            ],
            [
                'title' => 'Digital Literacy Workshops for Senior Citizens',
                'description' => 'A framework and curriculum designed for LGU adoption to teach digital literacy, mobile banking, and scam-prevention to the elderly.',
                'authors' => 'Maria Santos',
                'views' => 305,
                'goals' => [4, 11]
            ],
            [
                'title' => 'Micro-Hydro Generators for Irrigation Canals',
                'description' => 'Designing a standardized, drop-in micro-hydroelectric generator suitable for the flow rates of agricultural irrigation canals in Central Luzon.',
                'authors' => 'Rome Arist Mendoza',
                'views' => 1670,
                'goals' => [7, 9]
            ],
        ];

        foreach ($thesesData as $data) {
            $thesis = new Thesis();
            $thesis->setTitle($data['title'])
                   ->setDescription($data['description'])
                   ->setAuthors($data['authors'])
                   ->setViews($data['views'])
                   ->setDocumentFile('dummy-thesis.pdf')
                   ->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
            
            // Link the specific SDGs to this thesis
            foreach ($data['goals'] as $goalNum) {
                $thesis->addSdgGoal($sdgEntities[$goalNum]);
            }
            $manager->persist($thesis);
        }

        // 3. Create Activities / News (Pulled from FTIC DB Articles)
        $activitiesData = [
            [
                'title' => 'Capacity Building: Patent Search Workshop',
                'category' => 'Seminar',
                'content' => '<p>An in-depth capability building workshop for engineering students to understand patent searches, intellectual property rights, and how to protect their technological innovations.</p>',
                'date' => '2026-03-04'
            ],
            [
                'title' => 'TamLabs Demo Day: Celebrating Six Months of Innovation, Grit, and Growth',
                'category' => 'News',
                'content' => '<p>The FEU Tech Innovation Center\'s ongoing efforts to create spaces where <strong>technology, creativity, and human-centered innovation</strong> meet. It showcased how Filipino innovators—when equipped with education, mentorship, and opportunity—can build solutions that matter both locally and internationally.</p>',
                'date' => '2025-10-28'
            ],
            [
                'title' => 'Hustlers: Student Entrepreneurship Program',
                'category' => 'Workshop',
                'content' => '<p>Creates opportunities for students to test their ideas, receive customer feedback, and build confidence as business owners. Through Hustlers, FEU Tech nurtures student interest in <strong>sustainability, innovation, and impact-driven careers</strong>, underscoring FTIC’s role in fostering environments where young leaders can grow their skills and lay the groundwork for a greener future.</p>',
                'date' => '2024-12-02'
            ]
        ];

        foreach ($activitiesData as $data) {
            $activity = new Activity();
            $activity->setTitle($data['title'])
                     ->setCategory($data['category'])
                     ->setContent($data['content'])
                     ->setEventDate(new \DateTime($data['date']))
                     ->setCreatedAt(new \DateTimeImmutable($data['date']));
            $manager->persist($activity);
        }

        // Save everything to the database
        $manager->flush();
    }
}