<?php

namespace App\Controller\Admin;

use App\Entity\Sdg;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
        ];
    }
}