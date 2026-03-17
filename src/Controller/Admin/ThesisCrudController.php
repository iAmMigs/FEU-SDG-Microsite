<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ThesisCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thesis::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextField::new('authors'),
            
            // Textarea is perfect for abstracts
            TextareaField::new('description')->hideOnIndex(),
            
            // This automatically looks up the SdgGoal database and creates a multi-select box!
            AssociationField::new('sdgGoals')
                ->setFormTypeOptions(['by_reference' => false]),
            
            // Hide these fields from the form so they are automatically handled
            IntegerField::new('views')->hideOnForm(),
            DateTimeField::new('createdAt')->hideOnForm(),

            // Cover Image Uploader
            ImageField::new('coverImage')
                ->setBasePath('uploads/theses')
                ->setUploadDir('public/uploads/theses')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            
            ImageField::new('documentFile', 'PDF Document')
                ->setBasePath('uploads/theses/documents')
                ->setUploadDir('public/uploads/theses/documents')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->hideOnIndex() // Hides it on the list view so it doesn't try to render a PDF as an image
                ->setRequired(false),
        ];
    }
}