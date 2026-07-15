<?php

namespace App\Controller\Admin;

use App\Entity\SubmissionRequirement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class SubmissionRequirementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubmissionRequirement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Submission Requirement')
            ->setEntityLabelInPlural('Submission Requirements')
            ->setDefaultSort(['type' => 'ASC', 'sortOrder' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('type', 'Requirement Category')
                ->setChoices([
                    'Project' => 'project',
                    'Event/Activity' => 'event',
                ])
                ->setRequired(true),
            TextField::new('title', 'Requirement Title')
                ->setHelp('e.g., Researchers, Study, Number of Pax, Venue'),
            TextareaField::new('description', 'Description (Italicized on page)')
                ->setHelp('Brief details about this requirement (optional)'),
            IntegerField::new('sortOrder', 'Sort Order')
                ->setHelp('Lower numbers appear first'),
        ];
    }
}
