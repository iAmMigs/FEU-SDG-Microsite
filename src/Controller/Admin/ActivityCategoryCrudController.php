<?php

namespace App\Controller\Admin;

use App\Entity\ActivityCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ActivityCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ActivityCategory::class;
    }
}