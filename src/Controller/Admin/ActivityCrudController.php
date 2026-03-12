<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
            
            // Creates a nice dropdown instead of making admins type the word perfectly
            ChoiceField::new('category')->setChoices([
                'News' => 'News',
                'Seminar' => 'Seminar',
                'Workshop' => 'Workshop',
            ]),
            
            DateTimeField::new('eventDate'),
            
            // This turns the text field into a secure File Uploader!
            ImageField::new('image')
                ->setBasePath('uploads/activities') // Where the browser looks for the image
                ->setUploadDir('public/uploads/activities') // Where the server saves the image
                ->setUploadedFileNamePattern('[randomhash].[extension]') // Secures the file name
                ->setRequired(false),
                
            // Adds a Word-like rich text editor for the article body
            TextEditorField::new('content'),
        ];
    }
}