<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\QueryBuilder;

class ThesisCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thesis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(20)
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $thesis = $entityDto->getInstance();

        $projectDir = $this->getParameter('kernel.project_dir');

        // Check and clean Cover Image mapping
        if ($thesis->getCoverImage()) {
            $imagePath = $projectDir . '/public/uploads/theses/' . $thesis->getCoverImage();
            if (!file_exists($imagePath)) {
                $thesis->setCoverImage(null);
            }
        }

        // Check and clean Document File mapping
        if ($thesis->getDocumentFile()) {
            $docPath = $projectDir . '/public/uploads/theses/' . $thesis->getDocumentFile();
            if (!file_exists($docPath)) {
                $thesis->setDocumentFile(null);
            }
        }

        return $formBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            BooleanField::new('isActive', 'Active'),

            TextField::new('title', 'Thesis Title'),
            TextField::new('authors', 'Authors'),
            TextareaField::new('description', 'Abstract / Description')
                ->setNumOfRows(6)
                ->hideOnIndex(),
            
            AssociationField::new('sdgs', 'Targeted SDGs')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.id', 'ASC');
                })
                ->autocomplete(),

            UrlField::new('publicationLink', 'External Publication Link')
                ->setHelp('Optional URL to an external journal or publication')
                ->setRequired(false),
            
            TextField::new('documentFile', 'PDF Document')
                ->setFormType(FileUploadType::class)
                ->setFormTypeOptions([
                    'upload_dir' => 'public/uploads/theses',
                    'upload_filename' => '[randomhash].[extension]',
                    'attr' => [
                        'accept' => 'application/pdf'
                    ]
                ])
                ->hideOnIndex(),
            
            ImageField::new('coverImage', 'Cover Image')
                ->setBasePath('uploads/theses')
                ->setUploadDir('public/uploads/theses')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/jpeg, image/png, image/webp' 
                    ]
                ]),

            IntegerField::new('views')->hideOnForm(),
            DateTimeField::new('createdAt', 'Date Added')->hideOnForm(),
        ];
    }
}