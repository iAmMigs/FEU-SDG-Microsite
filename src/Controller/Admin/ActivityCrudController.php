<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ActivityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activity::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(10)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setDefaultRowAction(null);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $qb->leftJoin('entity.category', 'cat')->addSelect('cat')
           ->leftJoin('entity.college', 'c')->addSelect('c')
           ->leftJoin('entity.sdgs', 's')->addSelect('s');
        
        return $qb;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToHead('<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>')
            ->addHtmlContentToBody("
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        /**
                         * Initializes the TinyMCE rich text editor and configures the backend image upload endpoint.
                         */
                        if (typeof tinymce !== 'undefined') {
                            tinymce.init({
                                selector: '.tinymce-wrapper textarea',
                                height: 600,
                                plugins: 'image link lists media table wordcount',
                                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image',
                                images_upload_handler: function (blobInfo, progress) {
                                    return new Promise((resolve, reject) => {
                                        const xhr = new XMLHttpRequest();
                                        xhr.withCredentials = false;
                                        xhr.open('POST', '/admin/upload-image');
                                        
                                        xhr.upload.onprogress = (e) => progress(e.loaded / e.total * 100);
                                        
                                        xhr.onload = () => {
                                            if (xhr.status === 404) return reject('Upload endpoint not found.');
                                            if (xhr.status < 200 || xhr.status >= 300) return reject('HTTP Error: ' + xhr.status);
                                            const json = JSON.parse(xhr.responseText);
                                            if (!json || typeof json.location != 'string') return reject('Invalid JSON format.');
                                            resolve(json.location);
                                        };
                                        xhr.onerror = () => reject('Network transport error.');
                                        
                                        const formData = new FormData();
                                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                                        xhr.send(formData);
                                    });
                                },
                                menubar: false, statusbar: true, branding: false, promotion: false,
                                skin: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oxide-dark' : 'oxide'),
                                content_css: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default'),
                                image_class_list: [{title: 'Responsive', value: 'max-w-full h-auto rounded-2xl shadow-lg my-4'}],
                                setup: function (editor) {
                                    editor.on('change keyup', function () { editor.save(); });
                                }
                            });
                        }

                        /**
                         * Attaches listeners to the DOM to ensure logical parity between the 'Active' toggle 
                         * and the 'Scheduled Release' date parameters before form submission.
                         */
                        ['change', 'input'].forEach(evt => {
                            document.body.addEventListener(evt, function(e) {
                                if (e.target && e.target.id === 'Activity_isActive') {
                                    const publishAtInput = document.getElementById('Activity_publishAt');
                                    if (publishAtInput && !e.target.checked) {
                                        // Keeping user custom future dates if intentionally scheduling
                                    }
                                }
                            });
                        });
                    });
                </script>
                <style>
                    .tox-notifications-container { display: none !important; }
                    .tox-tinymce { width: 100% !important; border-radius: 0.5rem !important; border: 1px solid #374151 !important; }
                </style>
            ");
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Article Title'),
            TextField::new('category', 'Category')
                ->hideOnForm()
                ->formatValue(function ($value) {
                    return $value ? (string) $value : '<span class="text-muted small">Not Specified</span>';
                }),
            AssociationField::new('category', 'Category')->setRequired(true)->hideOnIndex(),
            TextField::new('college', 'College')
                ->hideOnForm()
                ->formatValue(function ($value) {
                    return $value ? (string) $value : '<span class="text-muted small">Not Specified</span>';
                }),
            AssociationField::new('college', 'College')->setRequired(false)->hideOnIndex(),
            ImageField::new('image', 'Main Cover Image')
                ->setBasePath('uploads/activities/')
                ->setUploadDir('public/uploads/activities/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->hideOnIndex()
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/jpeg, image/png, image/webp, image/gif' 
                    ]
                ]),
            DateTimeField::new('eventDate', 'Event Date')->setFormat('yyyy-MM-dd HH:mm'),
            AssociationField::new('sdgs', 'Focus SDGs')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb->orderBy('entity.id', 'ASC');
                })
                ->setTemplatePath('Admin-Microsite/fields/sdg_tags.html.twig')
                ->setFormTypeOptions(['by_reference' => false])
                ->setHelp('Select the Sustainable Development Goals associated with this activity.'),
            BooleanField::new('isActive', 'Active'),
            DateTimeField::new('publishAt', 'Schedule Release')
                ->hideOnIndex()
                ->setHelp('Leave blank to publish now. Set a future date to auto-release later.'),
            TextareaField::new('content', 'Article Content')
                ->setColumns(12) 
                ->hideOnIndex()
                ->addCssClass('tinymce-wrapper') 
                ->setHelp('Type your article here. Use the toolbar to style text and drop images.'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Activity) {
            $this->applyPublishLogic($entityInstance, null);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Activity) {
            $this->applyPublishLogic($entityInstance, $entityManager);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Synchronizes the Active toggle and Scheduled Release date fields prior to database operations.
     * Prevents conflicts where an entity is active but has a future release timestamp.
     */
    private function applyPublishLogic(Activity $activity, ?EntityManagerInterface $em): void
    {
        $now = new \DateTime();

        if ($em && $activity->getId()) {
            $originalData = $em->getUnitOfWork()->getOriginalEntityData($activity);
            
            $wasActive = $originalData['isActive'] ?? false;
            $oldPublishAt = $originalData['publishAt'] ?? null;
            
            $isNowActive = $activity->isActive();
            $newPublishAt = $activity->getPublishAt();

            if (!$wasActive && $isNowActive) {
                $activity->setPublishAt($now);
            } elseif ($newPublishAt && $newPublishAt > $now && $oldPublishAt != $newPublishAt) {
                $activity->setIsActive(false);
            } else {
                if ($isNowActive && (!$newPublishAt || $newPublishAt > $now)) {
                    $activity->setPublishAt($now);
                } elseif ($newPublishAt && $newPublishAt > $now) {
                    $activity->setIsActive(false);
                }
            }
        } else {
            if ($activity->getPublishAt() && $activity->getPublishAt() > $now) {
                $activity->setIsActive(false); 
            } elseif ($activity->isActive()) {
                $activity->setPublishAt($now); 
            }
        }
    }
}