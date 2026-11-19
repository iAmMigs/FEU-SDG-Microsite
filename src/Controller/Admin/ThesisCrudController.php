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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\QueryBuilder;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class ThesisCrudController extends AbstractCrudController
{

    public function configureFilters(Filters $filters): Filters
    {
        $years = range((int)date('Y'), 1990);
        $yearChoices = array_combine($years, $years);

        return $filters
            ->add(EntityFilter::new('college', 'College'))
            ->add(EntityFilter::new('type', 'Publication Type'))
            ->add(ChoiceFilter::new('researchYear', 'Year of Research')->setChoices($yearChoices));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $qb->leftJoin('entity.college', 'c')->addSelect('c')
           ->leftJoin('entity.type', 'pt')->addSelect('pt')
           ->leftJoin('entity.sdgs', 's')->addSelect('s');
        
        $request = $this->getContext()->getRequest();
        $researchYear = $request->query->get('researchYear');
        
        if ($researchYear) {
            $qb->andWhere('entity.researchYear = :researchYear')
               ->setParameter('researchYear', (int) $researchYear);
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
            ->setDefaultRowAction(null)
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

        $batchManageAction = Action::new('batchManagement', 'Activate/Disable by Batch', 'fas fa-tasks')
            ->linkToUrl('#')
            ->setHtmlAttributes([
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#advancedBatchModal',
                'class' => 'btn btn-primary'
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
            ->add(Crud::PAGE_INDEX, $batchManageAction)
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
     * Fetches all necessary data to populate the advanced batch management filters.
     */
    #[Route('/admin/projects/batch-filter-data', name: 'admin_project_batch_filter_data', methods: ['GET'])]
    public function fetchBatchFilterData(EntityManagerInterface $entityManager): JsonResponse
    {
        $sdgs = $entityManager->getRepository(\App\Entity\Sdg::class)->findBy([], ['id' => 'ASC']);
        $types = $entityManager->getRepository(\App\Entity\ProjectType::class)->findBy([], ['name' => 'ASC']);
        $conn = $entityManager->getConnection();
        $years = $conn->fetchFirstColumn(
            'SELECT DISTINCT research_year AS yr FROM thesis WHERE research_year IS NOT NULL ORDER BY yr DESC'
        );

        return new JsonResponse([
            'sdgs' => array_map(fn($s) => ['id' => $s->getId(), 'name' => $s->getName(), 'number' => $s->getId()], $sdgs),
            'types' => array_map(fn($t) => ['id' => $t->getId(), 'name' => $t->getName()], $types),
            'years' => $years
        ]);
    }

    /**
     * Searches for projects matching advanced filter criteria for the batch tool.
     */
    #[Route('/admin/projects/batch-search', name: 'admin_project_batch_search', methods: ['POST'])]
    public function searchBatchProjects(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $keyword = $data['keyword'] ?? null;
        $sdgId = $data['sdgId'] ?? null;
        $year = $data['year'] ?? null;
        $typeId = $data['typeId'] ?? null;
        $isActive = $data['isActive'] ?? null;

        $qb = $entityManager->getRepository(Thesis::class)->createQueryBuilder('t')
            ->select('t.id', 't.title', 't.researchYear', 't.isActive');

        if ($keyword) {
            $qb->andWhere('t.title LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }
        if ($sdgId) {
            $qb->join('t.sdgs', 's')
               ->andWhere('s.id = :sdgId')
               ->setParameter('sdgId', $sdgId);
        }
        if ($year) {
            $qb->andWhere('t.researchYear = :year')
               ->setParameter('year', (int)$year);
        }
        if ($typeId) {
            $qb->andWhere('t.type = :typeId')
               ->setParameter('typeId', $typeId);
        }
        if ($isActive !== null && $isActive !== '') {
            $qb->andWhere('t.isActive = :isActive')
               ->setParameter('isActive', (bool)$isActive);
        }

        $projects = $qb->orderBy('t.researchYear', 'DESC')->addOrderBy('t.title', 'ASC')->getQuery()->getArrayResult();

        foreach ($projects as &$project) {
            $project['year'] = $project['researchYear'] ?? null;
        }

        return new JsonResponse($projects);
    }

    /**
     * Executes the bulk activation/deactivation of targeted projects.
     */
    #[Route('/admin/projects/batch-execute-toggle', name: 'admin_project_batch_execute_toggle', methods: ['POST'])]
    public function executeBatchStatusUpdate(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];
        $targetStatus = $data['targetStatus'] ?? false;

        if (empty($ids)) {
            return new JsonResponse(['success' => false, 'message' => 'No projects selected.'], 400);
        }

        $entityManager->createQueryBuilder()
            ->update(Thesis::class, 't')
            ->set('t.isActive', ':status')
            ->where('t.id IN (:ids)')
            ->setParameter('status', (bool)$targetStatus)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        return new JsonResponse(['success' => true, 'count' => count($ids)]);
    }

    /**
     * Executes the bulk deletion of targeted projects.
     */
    #[Route('/admin/projects/batch-execute-delete', name: 'admin_project_batch_execute_delete', methods: ['POST'])]
    public function executeBatchDelete(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return new JsonResponse(['success' => false, 'message' => 'No projects selected.'], 400);
        }

        $entityManager->createQueryBuilder()
            ->delete(Thesis::class, 't')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();

        return new JsonResponse(['success' => true, 'count' => count($ids)]);
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
                        
                        $project->setResearchYear((int) $researchYear);

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
            TextField::new('title', 'Title'),
            TextareaField::new('authors', 'Authors')
                ->setNumOfRows(3)
                ->setHelp('Separate multiple authors with a semicolon (;) to match our dataset format.')
                ->hideOnIndex(),
            
            TextField::new('type', 'Publication Type')
                ->hideOnForm()
                ->formatValue(function ($value) {
                    return $value ? (string) $value : '<span class="text-muted small">Not Specified</span>';
                }),
            AssociationField::new('type', 'Publication Type')
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('college', 'College')
                ->hideOnForm()
                ->formatValue(function ($value) {
                    return $value ? (string) $value : '<span class="text-muted small">Not Specified</span>';
                }),
            AssociationField::new('college', 'College')
                ->setRequired(false)
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
            
            IntegerField::new('researchYear', 'Year of Research')
                ->setHelp('Enter the year the research was conducted (e.g. 2024).')
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