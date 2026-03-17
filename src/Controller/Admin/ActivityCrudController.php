<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ActivityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            ChoiceField::new('category')->setChoices([
                'News' => 'News',
                'Seminar' => 'Seminar',
                'Workshop' => 'Workshop',
            ]),
            DateTimeField::new('eventDate'),
            ImageField::new('image')
                ->setBasePath('uploads/activities')
                ->setUploadDir('public/uploads/activities')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
                
            // Changed from TextEditorField to TextareaField!
            TextareaField::new('content')
                ->setNumOfRows(10),
        ];
    }
}