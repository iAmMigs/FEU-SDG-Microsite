<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Symfony\Component\Form\FormBuilderInterface;

class ThesisCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thesis::class;
    }

    /*
     * Intercepts the form builder process to verify the physical existence 
     * of media and document files before rendering the EasyAdmin edit interface.
     * Prevents fatal FileNotFound exceptions if assets are manually removed from the server.
     */
    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $thesis = $entityDto->getInstance();
        
        if ($thesis instanceof Thesis) {
            $publicDir = __DIR__ . '/../../../public/uploads/theses/';
            
            if ($thesis->getDocumentFile() && !file_exists($publicDir . $thesis->getDocumentFile())) {
                $thesis->setDocumentFile(null);
            }
            
            if ($thesis->getCoverImage() && !file_exists($publicDir . $thesis->getCoverImage())) {
                $thesis->setCoverImage(null);
            }
        }

        return parent::createEditFormBuilder($entityDto, $formOptions, $context);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Thesis Title'),
            TextField::new('authors', 'Authors'),
            TextareaField::new('description', 'Abstract')->hideOnIndex(),
            
            AssociationField::new('sdgs', 'SDG Tags')
                ->setFormTypeOptions(['by_reference' => false]),
            
            UrlField::new('publicationLink', 'External Publication Link')->setRequired(false),
            
            /*
             * Form configuration for the PDF Document field.
             * Utilizes FileUploadType to handle raw file transfers without image validation constraints.
             */
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
            
            /*
             * Form configuration for the Cover Image field.
             * Restricts acceptable file MIME types strictly to standard web image formats.
             */
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