<?php

namespace App\Controller\Admin;

use App\Entity\Thesis;
use App\Repository\SdgRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
use Symfony\Component\Routing\Attribute\Route;
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

    public function configureAssets(Assets $assets): Assets
    {
        /*
         * Injects a listener into the admin edit/new forms. It evaluates user input
         * in the publication link field in real-time. If the data matches a standard 
         * DOI format, it auto-prepends the domain to formalize it into a valid URL.
         */
        return parent::configureAssets($assets)->addHtmlContentToBody('
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const pubLinkInput = document.querySelector(\'input[name="Thesis[publicationLink]"]\');
                    if (pubLinkInput) {
                        pubLinkInput.addEventListener("input", function(e) {
                            let val = e.target.value.trim();
                            const doiRegex = /^10\.\d{4,9}\/[-._;()\/:a-zA-Z0-9]+$/i;
                            if (doiRegex.test(val)) {
                                e.target.value = "https://doi.org/" + val;
                            }
                        });
                    }
                });
            </script>
        ');
    }

    #[Route('/admin/projects/import-csv', name: 'admin_project_import_csv', methods: ['POST'])]
    public function importCsvAction(Request $request, EntityManagerInterface $entityManager, SdgRepository $sdgRepository, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $file = $request->files->get('csv_file');
        
        if ($file && $file->isValid() && strtolower($file->getClientOriginalExtension()) === 'csv') {
            $importedCount = 0;
            
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== false) {
                    
                    if (empty(array_filter($data))) continue;

                    $project = new Thesis();
                    $project->setTitle(trim($data[0] ?? ''));
                    $project->setAuthors(trim($data[1] ?? ''));
                    $project->setDescription(trim($data[2] ?? ''));
                    $project->setIsActive(false); 
                    $project->setViews(0);
                    $project->setRegionViews([]);

                    /*
                     * Evaluates the imported link against standard DOI registry formats.
                     * If it matches, the protocol and resolving host are automatically 
                     * appended before saving to the database.
                     */
                    $externalLink = trim($data[4] ?? '');
                    if ($externalLink !== '') {
                        if (preg_match('/^10\.\d{4,9}\/[-._;()\/:a-zA-Z0-9]+$/i', $externalLink)) {
                            $project->setPublicationLink('https://doi.org/' . $externalLink);
                        } else {
                            $project->setPublicationLink($externalLink);
                        }
                    }

                    $project->setType(!empty($data[5]) ? trim($data[5]) : null);
                    $project->setCollege(!empty($data[6]) ? trim($data[6]) : null);

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
            IdField::new('id')->hideOnForm()
            ->hideOnIndex(),
            BooleanField::new('isActive', 'Active'),
            TextField::new('title', 'Thesis Title'),
            TextField::new('authors', 'Authors'),
            ChoiceField::new('type', 'Type')->setChoices([
                'Article' => 'Article',
                'Book Chapter' => 'Book Chapter',
                'Letter' => 'Letter',
                'Proceedings' => 'Proceedings',
                'Review' => 'Review',
                'Editorial' => 'Editorial',
                'Book' => 'Book',
            ])->setRequired(false),
            TextField::new('college', 'College')->setRequired(false),
            TextareaField::new('description', 'Abstract / Description')->setNumOfRows(6)->hideOnIndex(),
            AssociationField::new('sdgs', 'Targeted SDGs')
                ->setFormTypeOptions(['by_reference' => false])
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.id', 'ASC');
                })->autocomplete(),
            UrlField::new('publicationLink', 'External Publication Link')
                ->setHelp('Paste a DOI or URL to an external journal or publication')
                ->setRequired(false),
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
            DateTimeField::new('createdAt', 'Date Added')->hideOnForm(),
        ];
    }
}