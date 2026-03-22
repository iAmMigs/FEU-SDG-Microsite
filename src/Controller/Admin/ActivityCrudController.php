<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
            ->setPaginatorPageSize(20)
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToHead('<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>')
            ->addHtmlContentToBody("
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // --- 1. TINYMCE INITIALIZATION ---
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

                        // --- 2. ACTIVE SWITCH VS. SCHEDULED DATE TOGGLE LOGIC ---
                        ['change', 'input'].forEach(evt => {
                            document.body.addEventListener(evt, function(e) {
                                if (!e.target || !e.target.name) return;

                                // SCENARIO A: Admin turns ON the Active switch -> Clear the Date inputs
                                if (e.target.name.includes('[isActive]') && e.target.checked) {
                                    document.querySelectorAll('input[name*=\"[publishAt]\"]').forEach(input => {
                                        input.value = '';
                                    });
                                }

                                // SCENARIO B: Admin types a Date -> Force the Active switch OFF
                                if (e.target.name.includes('[publishAt]') && e.target.value !== '') {
                                    const activeSwitch = document.querySelector('input[type=\"checkbox\"][name*=\"[isActive]\"]');
                                    if (activeSwitch && activeSwitch.checked) {
                                        activeSwitch.checked = false;
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
            ChoiceField::new('category', 'Category')->setChoices([
                'News' => 'News', 'Seminar' => 'Seminar', 'Community' => 'Community', 'Workshop' => 'Workshop', 'Project' => 'Project', 'Research' => 'Research',
            ]),
            ImageField::new('image', 'Main Cover Image')
                ->setBasePath('uploads/activities/')
                ->setUploadDir('public/uploads/activities/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->hideOnIndex(),
            DateTimeField::new('eventDate', 'Event Date')->setFormat('yyyy-MM-dd HH:mm'),
            AssociationField::new('sdgs', 'Focus SDGs')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb->andWhere('entity.isActive = :active')
                            ->setParameter('active', true);
                })
                ->setFormTypeOptions(['by_reference' => false])
                ->setHelp('Only active SDGs currently focused by the University are available.'),
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
     * Documentation: Intelligently synchronizes the Active switch and the Scheduled Release date.
     */
    private function applyPublishLogic(Activity $activity, ?EntityManagerInterface $em): void
    {
        $now = new \DateTime();

        if ($em && $activity->getId()) {
            // Get the original data from the DB before the Admin made their current edits
            $originalData = $em->getUnitOfWork()->getOriginalEntityData($activity);
            
            $wasActive = $originalData['isActive'] ?? false;
            $oldPublishAt = $originalData['publishAt'] ?? null;
            
            $isNowActive = $activity->isActive();
            $newPublishAt = $activity->getPublishAt();

            // RULE 1: Admin manually toggled "Active" from OFF to ON.
            // Fixes the bug: We clear the scheduled release by setting it to NOW so it appears instantly on the frontend.
            if (!$wasActive && $isNowActive) {
                $activity->setPublishAt($now);
            }
            // RULE 2: Admin manually changed the date to a FUTURE schedule.
            // Automatically turn OFF the active switch because it is waiting for its scheduled time.
            elseif ($newPublishAt && $newPublishAt > $now && $oldPublishAt != $newPublishAt) {
                $activity->setIsActive(false);
            }
            // RULE 3: Consistency fallback (Catch-all if both were altered strangely)
            else {
                if ($isNowActive && (!$newPublishAt || $newPublishAt > $now)) {
                    $activity->setPublishAt($now);
                } elseif ($newPublishAt && $newPublishAt > $now) {
                    $activity->setIsActive(false);
                }
            }
        } else {
            // RULE 4: Logic for creating a brand NEW Activity
            if ($activity->getPublishAt() && $activity->getPublishAt() > $now) {
                $activity->setIsActive(false); // Scheduled for later
            } elseif ($activity->isActive()) {
                $activity->setPublishAt($now); // Force publish now
            }
        }
    }
    
}