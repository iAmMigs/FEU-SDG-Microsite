<?php

namespace App\Controller\Admin;

use App\Entity\LeadingVoice;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class LeadingVoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LeadingVoice::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            ImageField::new('image', 'Profile Image (2x2 Ratio)')
                ->setBasePath('uploads/voices')
                ->setUploadDir('public/uploads/voices')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->setHelp('Upload a square 2x2 image. Will be automatically rounded on the frontend.'),
                
            TextField::new('name', 'Full Name'),
            
            TextField::new('title', 'Professional Title')
                ->setHelp('e.g., Lead Researcher, Engineering Director'),
            
            TextareaField::new('description', 'Description / Bio')
                ->setNumOfRows(6),
        ];
    }
}