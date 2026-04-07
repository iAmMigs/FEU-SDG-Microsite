<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use App\Repository\SdgRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // Standard Symfony Route
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
            ->setEntityLabelInSingular('Project')
            ->setEntityLabelInPlural('Projects')
            ->setPaginatorPageSize(20)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->overrideTemplate('crud/index', 'Admin-Microsite/thesis_index.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $importAction = Action::new('importMultiple', 'Add Multiple Projects', 'fas fa-file-csv')
            ->linkToUrl('#') 
            ->setHtmlAttributes([
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#csvImportModal',
                'class' => 'btn btn-secondary'
            ])
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $importAction);
    }

    /**
     * Using a standard route bypasses EasyAdmin's action blocking.
     */
    #[Route('/admin/projects/import-csv', name: 'admin_project_import_csv', methods: ['POST'])]
    public function importCsvAction(Request $request, EntityManagerInterface $entityManager, SdgRepository $sdgRepository, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $file = $request->files->get('csv_file');
        
        // Ensure the file exists and is a CSV
        if ($file && $file->isValid() && strtolower($file->getClientOriginalExtension()) === 'csv') {
            $importedCount = 0;
            
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                
                fgetcsv($handle); // Skip the header row
                
                while (($data = fgetcsv($handle)) !== false) {
                    
                    if (empty(array_filter($data))) continue;

                    $project = new Thesis();
                    $project->setTitle(trim($data[0] ?? ''));
                    $project->setAuthors(trim($data[1] ?? ''));
                    $project->setDescription(trim($data[2] ?? ''));
                    $project->setPublicationLink(trim($data[4] ?? ''));
                    $project->setIsActive(true);
                    $project->setViews(0);
                    $project->setRegionViews([]); // Initialize JSON column

                    // Map SDGs
                    $sdgColumn = trim($data[3] ?? '');
                    if ($sdgColumn !== '') {
                        $sdgIds = array_map('trim', explode(',', $sdgColumn));
                        foreach ($sdgIds as $sdgId) {
                            if (is_numeric($sdgId)) {
                                $sdg = $sdgRepository->find((int) $sdgId);
                                if ($sdg) {
                                    $project->addSdg($sdg);
                                }
                            }
                        }
                    }

                    $entityManager->persist($project);
                    $importedCount++;

                    // Batch processing to prevent memory limits
                    if ($importedCount % 50 === 0) {
                        $entityManager->flush();
                        $entityManager->clear(Thesis::class);
                    }
                }
                fclose($handle);
            }
            
            $entityManager->flush();
            $this->addFlash('success', "Successfully imported $importedCount projects.");
        } else {
            $this->addFlash('danger', 'Failed to upload or read the CSV file.');
        }

        // Redirect back to the Projects table
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $thesis = $entityDto->getInstance();

        $projectDir = $this->getParameter('kernel.project_dir');

        if ($thesis->getCoverImage()) {
            $imagePath = $projectDir . '/public/uploads/theses/' . $thesis->getCoverImage();
            if (!file_exists($imagePath)) {
                $thesis->setCoverImage(null);
            }
        }

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
            TextareaField::new('description', 'Abstract / Description')->setNumOfRows(6)->hideOnIndex(),
            AssociationField::new('sdgs', 'Targeted SDGs')
                ->setFormTypeOptions(['by_reference' => false])
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.id', 'ASC');
                })->autocomplete(),
            UrlField::new('publicationLink', 'External Publication Link')->setHelp('Optional URL to an external journal or publication')->setRequired(false),
            TextField::new('documentFile', 'PDF Document')
                ->setFormType(FileUploadType::class)
                ->setFormTypeOptions([
                    'upload_dir' => 'public/uploads/theses',
                    'upload_filename' => '[randomhash].[extension]',
                    'attr' => ['accept' => 'application/pdf']
                ])->hideOnIndex(),
            ImageField::new('coverImage', 'Cover Image')
                ->setBasePath('uploads/theses')
                ->setUploadDir('public/uploads/theses')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->setFormTypeOptions(['attr' => ['accept' => 'image/jpeg, image/png, image/webp']]),
            IntegerField::new('views')->hideOnForm(),
            DateTimeField::new('createdAt', 'Date Added')->hideOnForm(),
        ];
    }
}