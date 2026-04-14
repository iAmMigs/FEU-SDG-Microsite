<?php

namespace App\Controller\Admin;

use App\Entity\ActivityCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * Provides administrative management for dynamic Activity Categories.
 */
class ActivityCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ActivityCategory::class;
    }
}