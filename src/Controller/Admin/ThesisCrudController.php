<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Symfony\Component\Validator\Constraints\File;

class ThesisCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thesis::class;
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
            
            // THE CORRECT PDF FILE UPLOADER
            TextField::new('documentFile', 'PDF Document')
                ->setFormType(FileUploadType::class)
                ->setFormTypeOptions([
                    'upload_dir' => 'public/uploads/theses',
                    'upload_filename' => '[randomhash].[extension]',
                    'attr' => [
                        'accept' => 'application/pdf'
                    ],
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                'application/pdf',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF document.',
                        ])
                    ],
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