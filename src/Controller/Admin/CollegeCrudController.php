<?php

namespace App\Controller\Admin;

use App\Entity\College;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * Provides administrative management for dynamic College designations.
 */
class CollegeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return College::class;
    }
}