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
            
            // If the ID is in our active list, set it to true. Otherwise, false.
            $sdg->setIsActive(in_array($id, $activeSdgIds));
            
            $manager->persist($sdg);
            $sdgEntities[$id] = $sdg;
        }

        $loremIpsum = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\nDuis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

        // 2. Theses Data
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
                if (isset($sdgEntities[$goalNum])) {
                    $thesis->addSdg($sdgEntities[$goalNum]);
                }
            }
            $manager->persist($thesis);
        }

        // 3. Updated Activities Data
        $activitiesData = [
            [
                'title' => 'Capacity Building: Patent Search Workshop',
                'category' => 'Seminar',
                'content' => "<p>An in-depth capability building workshop for engineering students to understand patent searches, intellectual property rights, and how to protect their technological innovations.</p><p>Students will learn to use international databases to cross-reference their ideas.</p>",
                'date' => '2026-03-04',
                'goals' => [4, 9, 17],
                'isActive' => true
            ],
            [
                'title' => 'TamLabs Demo Day: Celebrating Six Months of Innovation',
                'category' => 'News',
                'content' => "<p>The FEU Tech Innovation Center's ongoing efforts to create spaces where technology, creativity, and human-centered innovation meet.</p><p>It showcased how Filipino innovators can build solutions that matter locally and internationally.</p>",
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