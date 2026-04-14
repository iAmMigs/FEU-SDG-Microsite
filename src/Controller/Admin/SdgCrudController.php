<?php

namespace App\Controller\Admin;

use App\Entity\Sdg;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

/**
 * Manages the Sustainable Development Goals (SDG) categories and their visibility across the platform.
 */
class SdgCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sdg::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id', 'SDG Number'),
            TextField::new('name', 'SDG Title'),
            BooleanField::new('isActive', 'Active Focus Area')
                ->setHelp('Enable this to unlock the SDG in the Library and on the Home page buttons.'),
        ];
    }
}