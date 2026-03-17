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
        foreach ($sdgData as $id => $name) {
            $sdg = new Sdg();
            $sdg->setId($id);
            $sdg->setName($name);
            $manager->persist($sdg);
            $sdgEntities[$id] = $sdg;
        }

        $loremIpsum = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\nDuis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\n\nCurabitur pretium tincidunt lacus. Nulla gravida orci a odio. Nullam varius, turpis et commodo pharetra, est eros bibendum elit, nec luctus magna felis sollicitudin mauris. Integer in mauris eu nibh euismod gravida. Duis ac tellus et risus vulputate vehicula.";

        $thesesData = [
            [
                'title' => 'Incer-Eco Tech: Waste-to-Energy Incinerator',
                'description' => $loremIpsum,
                'authors' => 'Engr. Jose Florenz Somigao, Rome Arist Mendoza',
                'views' => 1405,
                'goals' => [7, 11, 12]
            ],
            [
                'title' => 'Impact of AI-Assisted Tutoring on Engineering Students',
                'description' => $loremIpsum,
                'authors' => 'Anna Reyes',
                'views' => 1204,
                'goals' => [4]
            ],
            [
                'title' => 'Solar-Powered Water Filtration for Rural Communities',
                'description' => $loremIpsum,
                'authors' => 'J. Dela Cruz',
                'views' => 892,
                'goals' => [3, 7]
            ],
            [
                'title' => 'Smart Traffic Management using Computer Vision',
                'description' => $loremIpsum,
                'authors' => 'Maria Santos, K. Villanueva',
                'views' => 2100,
                'goals' => [9, 11]
            ],
            [
                'title' => 'Recycled Plastic as Aggregate in Concrete Mixtures',
                'description' => $loremIpsum,
                'authors' => 'Engr. L. Bautista',
                'views' => 1750,
                'goals' => [9, 11, 12]
            ],
            [
                'title' => 'Predictive Maintenance for Wind Turbines',
                'description' => $loremIpsum,
                'authors' => 'P. Fernandez',
                'views' => 640,
                'goals' => [7, 9]
            ],
            [
                'title' => 'Urban Farming: Automated Aquaponics System',
                'description' => $loremIpsum,
                'authors' => 'R. Garcia',
                'views' => 310,
                'goals' => [3, 11, 12]
            ],
            [
                'title' => 'Blockchain for Transparent Supply Chains',
                'description' => $loremIpsum,
                'authors' => 'S. Lim, T. Tan',
                'views' => 950,
                'goals' => [8, 12]
            ],
            [
                'title' => 'Telemedicine Platform for Remote Barangays',
                'description' => $loremIpsum,
                'authors' => 'Dr. C. Ocampo, Anna Reyes',
                'views' => 1820,
                'goals' => [3, 11]
            ],
            [
                'title' => 'Gamification of STEM Education for Grade Schoolers',
                'description' => $loremIpsum,
                'authors' => 'M. Torres',
                'views' => 420,
                'goals' => [4]
            ],
            [
                'title' => 'Optimization of Jeepney Routes via Genetic Algorithms',
                'description' => $loremIpsum,
                'authors' => 'J. Dela Cruz',
                'views' => 1150,
                'goals' => [11]
            ],
            [
                'title' => 'Flood Prediction using River Sensors',
                'description' => $loremIpsum,
                'authors' => 'Engr. Jose Florenz Somigao',
                'views' => 2500,
                'goals' => [9, 11]
            ],
            [
                'title' => 'Biodegradable Packaging from Banana Pseudostem',
                'description' => $loremIpsum,
                'authors' => 'A. Ramos',
                'views' => 880,
                'goals' => [12]
            ],
            [
                'title' => 'Digital Literacy Workshops for Senior Citizens',
                'description' => $loremIpsum,
                'authors' => 'Maria Santos',
                'views' => 305,
                'goals' => [4, 11]
            ],
            [
                'title' => 'Micro-Hydro Generators for Irrigation Canals',
                'description' => $loremIpsum,
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
            
            foreach ($data['goals'] as $goalNum) {
                $thesis->addSdg($sdgEntities[$goalNum]);
            }
            $manager->persist($thesis);
        }

        $activitiesData = [
            [
                'title' => 'Capacity Building: Patent Search Workshop',
                'category' => 'Seminar',
                'content' => "An in-depth capability building workshop for engineering students to understand patent searches, intellectual property rights, and how to protect their technological innovations.\n\nStudents will learn to use international databases.",
                'date' => '2026-03-04'
            ],
            [
                'title' => 'TamLabs Demo Day: Celebrating Six Months of Innovation, Grit, and Growth',
                'category' => 'News',
                'content' => "The FEU Tech Innovation Center's ongoing efforts to create spaces where technology, creativity, and human-centered innovation meet.\n\nIt showcased how Filipino innovators can build solutions that matter locally and internationally.",
                'date' => '2025-10-28'
            ],
            [
                'title' => 'Hustlers: Student Entrepreneurship Program',
                'category' => 'Workshop',
                'content' => "Creates opportunities for students to test their ideas, receive customer feedback, and build confidence as business owners.\n\nThrough Hustlers, FEU Tech nurtures student interest in sustainability, innovation, and impact-driven careers.",
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

        $manager->flush();
    }
}