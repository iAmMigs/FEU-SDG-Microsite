<?php

namespace App\Controller\Admin;

use App\Entity\ProjectType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * Provides administrative management for dynamic Project Types.
 */
class ProjectTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectType::class;
    }
}