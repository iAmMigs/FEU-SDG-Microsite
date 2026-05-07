<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use App\Entity\ProjectType;
use App\Entity\College;
use App\Repository\SdgRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
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
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\QueryBuilder;

class ThesisCrudController extends AbstractCrudController
{

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $request = $this->getContext()->getRequest();
        $researchYear = $request->query->get('researchYear');
        
        if ($researchYear) {
            $qb->andWhere('entity.researchDate = :year')
               ->setParameter('year', (int)$researchYear);
        }
        
        return $qb;
    }

    public static function getEntityFqcn(): string
    {
        return Thesis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Project')
            ->setEntityLabelInPlural('Projects')
            ->setPaginatorPageSize(10)
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

        $batchActivate = Action::new('batchActivate', 'Activate Selected', 'fas fa-check-circle')
            ->linkToRoute('admin_project_batch_activate')
            ->addCssClass('btn btn-success');

        $batchDeactivate = Action::new('batchDeactivate', 'Deactivate Selected', 'fas fa-times-circle')
            ->linkToRoute('admin_project_batch_deactivate')
            ->addCssClass('btn btn-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, $importAction)
            ->addBatchAction($batchActivate)
            ->addBatchAction($batchDeactivate);
    }

    /**
     * Batch-activates all selected thesis/project records.
     */
    #[Route('/admin/projects/batch-activate', name: 'admin_project_batch_activate', methods: ['POST'])]
    public function batchActivateAction(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $batchActionDto = new BatchActionDto(
            $context->getRequest()->request->get(EA::BATCH_ACTION_NAME),
            $context->getRequest()->request->all()[EA::BATCH_ACTION_ENTITY_IDS] ?? [],
            $context->getRequest()->request->get(EA::ENTITY_FQCN),
            $context->getRequest()->request->get(EA::BATCH_ACTION_CSRF_TOKEN)
        );

        $ids = $batchActionDto->getEntityIds();
        foreach ($ids as $id) {
            $thesis = $entityManager->getRepository(Thesis::class)->find($id);
            if ($thesis) {
                $thesis->setIsActive(true);
            }
        }
        $entityManager->flush();
        $this->addFlash('success', sprintf('%d project(s) activated successfully.', count($ids)));

        return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    /**
     * Batch-deactivates all selected thesis/project records.
     */
    #[Route('/admin/projects/batch-deactivate', name: 'admin_project_batch_deactivate', methods: ['POST'])]
    public function batchDeactivateAction(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $batchActionDto = new BatchActionDto(
            $context->getRequest()->request->get(EA::BATCH_ACTION_NAME),
            $context->getRequest()->request->all()[EA::BATCH_ACTION_ENTITY_IDS] ?? [],
            $context->getRequest()->request->get(EA::ENTITY_FQCN),
            $context->getRequest()->request->get(EA::BATCH_ACTION_CSRF_TOKEN)
        );

        $ids = $batchActionDto->getEntityIds();
        foreach ($ids as $id) {
            $thesis = $entityManager->getRepository(Thesis::class)->find($id);
            if ($thesis) {
                $thesis->setIsActive(false);
            }
        }
        $entityManager->flush();
        $this->addFlash('warning', sprintf('%d project(s) deactivated.', count($ids)));

        return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    /**
     * Processes the uploaded CSV file to import multiple project records.
     * Validates headers dynamically and batches database flushes to optimize memory usage.
     */
    #[Route('/admin/projects/import-csv', name: 'admin_project_import_csv', methods: ['POST'])]
    public function importCsvAction(Request $request, EntityManagerInterface $entityManager, SdgRepository $sdgRepository, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $file = $request->files->get('csv_file');
        $researchYear = $request->request->get('research_year');
        
        if ($file && $file->isValid() && strtolower($file->getClientOriginalExtension()) === 'csv') {
            
            ini_set('auto_detect_line_endings', true);
            $importedCount = 0;
            $skippedCount = 0;
            
            try {
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    
                    $firstLine = fgets($handle);
                    if (!$firstLine) {
                        throw new \Exception("The CSV file appears to be empty.");
                    }
                    
                    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                    rewind($handle);
                    
                    $headers = fgetcsv($handle, 0, $delimiter);
                    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]); 
                    
                    $headerMap = [];
                    foreach ($headers as $index => $header) {
                        $cleanHeader = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header);
                        $normalized = strtolower(trim($cleanHeader));
                        if (!empty($normalized)) {
                            $headerMap[$normalized] = $index;
                        }
                    }
                    
                    $authorsIdx  = $headerMap['authors'] ?? $headerMap['author'] ?? -1;
                    $titleIdx    = $headerMap['title'] ?? -1;
                    $abstractIdx = $headerMap['abstract'] ?? $headerMap['description'] ?? -1;
                    $doiIdx      = $headerMap['doi'] ?? -1;
                    $typeIdx     = $headerMap['type'] ?? $headerMap['projecttype'] ?? -1;
                    $collegeIdx  = $headerMap['college'] ?? -1;
                    $sdgIdx      = $headerMap['sdg'] ?? $headerMap['targetsdgs'] ?? -1;

                    if ($titleIdx === -1 || $authorsIdx === -1) {
                        $foundHeaders = implode(' | ', array_keys($headerMap));
                        throw new \Exception("Header Mismatch. Detected Delimiter: '$delimiter'. Found: [ $foundHeaders ]. Columns must include 'Authors' and 'Title'.");
                    }
                    
                    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                        if (empty(array_filter($data))) continue;

                        $title = trim($data[$titleIdx] ?? '');
                        if (empty($title)) {
                            $skippedCount++;
                            continue; 
                        }

                        $project = new Thesis();
                        $project->setTitle($title);
                        if ($authorsIdx !== -1) $project->setAuthors(trim($data[$authorsIdx] ?? ''));
                        if ($abstractIdx !== -1) $project->setDescription(trim($data[$abstractIdx] ?? ''));
                        if ($doiIdx !== -1) $project->setDoi(trim($data[$doiIdx] ?? ''));
                        
                        $project->setIsActive(false); 
                        $project->setViews(0);
                        $project->setRegionViews([]);
                        $project->setResearchDate((int) $researchYear);

                        if ($typeIdx !== -1 && !empty(trim($data[$typeIdx] ?? ''))) {
                            $typeStr = trim($data[$typeIdx]);
                            $type = $entityManager->getRepository(ProjectType::class)->findOneBy(['name' => $typeStr]);
                            if (!$type) {
                                $type = new ProjectType();
                                $type->setName($typeStr);
                                $entityManager->persist($type);
                                $entityManager->flush();
                            }
                            $project->setType($type);
                        }

                        if ($collegeIdx !== -1 && !empty(trim($data[$collegeIdx] ?? ''))) {
                            $collegeStr = trim($data[$collegeIdx]);
                            $college = $entityManager->getRepository(College::class)->findOneBy(['name' => $collegeStr]);
                            if (!$college) {
                                $college = new College();
                                $college->setName($collegeStr);
                                $entityManager->persist($college);
                                $entityManager->flush();
                            }
                            $project->setCollege($college);
                        }

                        if ($sdgIdx !== -1 && !empty(trim($data[$sdgIdx] ?? ''))) {
                            $sdgIds = array_map('trim', explode(',', trim($data[$sdgIdx])));
                            foreach ($sdgIds as $sdgId) {
                                if (is_numeric($sdgId)) {
                                    $sdg = $sdgRepository->find((int) $sdgId);
                                    if ($sdg) $project->addSdg($sdg);
                                }
                            }
                        }

                        $entityManager->persist($project);
                        $importedCount++;

                        if ($importedCount % 50 === 0) {
                            $entityManager->flush();
                            $entityManager->clear();
                        }
                    }
                    fclose($handle);
                }
                
                $entityManager->flush();
                $msg = "Successfully imported $importedCount projects.";
                if ($skippedCount > 0) {
                    $msg .= " (Skipped $skippedCount rows missing a title).";
                }
                $this->addFlash('success', $msg);
                
            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
            
        } else {
            $this->addFlash('danger', 'Failed to upload. Ensure the file is saved as a .csv format.');
        }

        $url = $adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl();
        return $this->redirect($url);
    }

    /**
     * Validates file existence on the server when constructing the edit form.
     * Automatically clears missing file references from the entity to prevent broken links.
     */
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
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            BooleanField::new('isActive', 'Active'),
            TextField::new('title', 'Thesis Title'),
            TextField::new('authors', 'Authors')
                ->setHelp('Separate multiple authors with a semicolon (;) to match our dataset format.')
                ->hideOnIndex(),
            
            AssociationField::new('type', 'Type')
                ->setRequired(false)
                ->formatValue(function ($value) {
                    return $value ?: '<span class="text-muted small">Not Specified</span>';
                }),
            AssociationField::new('college', 'College')->setRequired(false)
                ->hideOnIndex(),
            
            TextareaField::new('description', 'Abstract')->setNumOfRows(6)->hideOnIndex()->setRequired(false),
            
            AssociationField::new('sdgs', 'Targeted SDGs')
                ->setTemplatePath('Admin-Microsite/fields/sdg_tags.html.twig')
                ->setFormTypeOptions(['by_reference' => false])
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.id', 'ASC');
                })->autocomplete(),
                
            TextField::new('doi', 'DOI')
                ->setHelp('Provide the Digital Object Identifier. (e.g. 10.1234/5678)')->setRequired(false),
            UrlField::new('publicationLink', 'External Publication Link')
                ->setHelp('Paste the URL to an external journal or publication if applicable')->setRequired(false)
                ->hideOnIndex(),
            
            ChoiceField::new('researchMonth', 'Date of Research')
                ->setChoices([
                    'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
                    'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
                    'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
                ])
                ->setHelp('Optional: Select a specific month (e.g., for manual entries).')
                ->setRequired(false)
                ->hideOnIndex(),
            
            ChoiceField::new('researchDate', 'Year of Research')
                ->setChoices(array_combine(range(date('Y'), 1990), range(date('Y'), 1990)))
                ->setHelp('Required: Select the year the research was conducted.')
                ->setRequired(true),
                
            TextField::new('documentFile', 'PDF Document')
                ->setFormType(FileUploadType::class)
                ->setFormTypeOptions([
                    'upload_dir' => 'public/uploads/theses',
                    'upload_filename' => '[randomhash].[extension]',
                    'attr' => ['accept' => 'application/pdf']
                ])->hideOnIndex(),
            ImageField::new('coverImage', 'Cover Image')
                ->hideOnIndex()
                ->setBasePath('uploads/theses')
                ->setUploadDir('public/uploads/theses')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->setFormTypeOptions(['attr' => ['accept' => 'image/jpeg, image/png, image/webp']]),
            IntegerField::new('views')->hideOnForm(),
            DateField::new('createdAt', 'Date Added')->hideOnForm()->hideOnIndex(),
        ];
    }

}