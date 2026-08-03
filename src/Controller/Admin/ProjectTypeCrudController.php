<?php

namespace App\Controller\Admin;

use App\Entity\ProjectType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Provides administrative management for dynamic Publication Types.
 */
class ProjectTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Publication Type')
            ->setEntityLabelInPlural('Publication Types');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::DELETE);
    }
}