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
            
            TextareaField::new('description')->hideOnIndex(),
            
            // CRITICAL FIX: This must say 'sdgs', not 'sdgGoals'
            AssociationField::new('sdgs', 'SDG Tags')
                ->setFormTypeOptions(['by_reference' => false]),
            
            IntegerField::new('views')->hideOnForm(),
            DateTimeField::new('createdAt')->hideOnForm(),

            ImageField::new('coverImage')
                ->setBasePath('uploads/theses')
                ->setUploadDir('public/uploads/theses')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
        ];
    }
}