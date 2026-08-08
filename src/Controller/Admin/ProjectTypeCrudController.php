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
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(static function (ProjectType $projectType) {
                    return $projectType->getTheses()->isEmpty();
                });
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->displayIf(static function (ProjectType $projectType) {
                    return $projectType->getTheses()->isEmpty();
                });
            });
    }
}