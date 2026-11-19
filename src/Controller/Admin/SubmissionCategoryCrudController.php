<?php

namespace App\Controller\Admin;

use App\Entity\SubmissionCategory;
use App\Form\SubmissionStepType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class SubmissionCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubmissionCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Requirement Category')
            ->setEntityLabelInPlural('Requirement Categories')
            ->setDefaultSort(['id' => 'ASC'])
            ->setDefaultRowAction(null);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Category Name')
                ->setHelp('e.g., Project, Event/Activity, Workshop Request'),
            TextareaField::new('description', 'Category Description')
                ->setHelp('Brief summary explaining what this category is for (optional)'),
            CollectionField::new('steps', 'Required Information')
                ->setEntryType(SubmissionStepType::class)
                ->allowAdd()
                ->allowDelete()
                ->setEntryToStringMethod(function ($value) {
                    if (is_array($value) && !empty($value['title'])) {
                        return 'Info: ' . $value['title'];
                    }
                    return 'Info Item';
                })
                ->formatValue(function ($value) {
                    if (!is_array($value) || empty($value)) {
                        return '0 Items';
                    }
                    $count = count($value);
                    return $count . ' Item' . ($count > 1 ? 's' : '');
                })
                ->setHelp('Add, remove, or reorder the required information for this category.'),
        ];
    }
}
