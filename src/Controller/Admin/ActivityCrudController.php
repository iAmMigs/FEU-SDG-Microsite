<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ActivityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Activity::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addHtmlContentToHead('<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>')
            ->addHtmlContentToBody("
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof tinymce !== 'undefined') {
                            tinymce.init({
                                selector: '.tinymce-wrapper textarea',
                                height: 600,
                                plugins: 'image link lists media table wordcount',
                                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image',
                                
                                // THE FIX: Robust Promise-based upload handler
                                images_upload_handler: function (blobInfo, progress) {
                                    return new Promise((resolve, reject) => {
                                        const xhr = new XMLHttpRequest();
                                        xhr.withCredentials = false;
                                        xhr.open('POST', '/admin/upload-image');
                                        
                                        xhr.upload.onprogress = (e) => {
                                            progress(e.loaded / e.total * 100);
                                        };
                                        
                                        xhr.onload = () => {
                                            if (xhr.status === 404) {
                                                reject('Upload endpoint not found. Verify the ImageUploadController route.');
                                                return;
                                            }
                                            if (xhr.status < 200 || xhr.status >= 300) {
                                                reject('HTTP Error: ' + xhr.status);
                                                return;
                                            }
                                            
                                            const json = JSON.parse(xhr.responseText);
                                            if (!json || typeof json.location != 'string') {
                                                reject('Invalid JSON format from server.');
                                                return;
                                            }
                                            resolve(json.location);
                                        };
                                        
                                        xhr.onerror = () => {
                                            reject('Image upload failed due to a network transport error.');
                                        };
                                        
                                        const formData = new FormData();
                                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                                        xhr.send(formData);
                                    });
                                },
                                
                                menubar: false,
                                statusbar: true,
                                branding: false,
                                promotion: false,
                                skin: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oxide-dark' : 'oxide'),
                                content_css: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default'),
                                
                                image_class_list: [
                                    {title: 'Responsive (Full Width)', value: 'w-full h-auto rounded-2xl shadow-lg my-10 block mx-auto'}
                                ],
                                
                                setup: function (editor) {
                                    editor.on('change keyup', function () {
                                        editor.save();
                                    });
                                }
                            });
                        }
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
                'News' => 'News', 'Seminar' => 'Seminar', 'Community' => 'Community', 'Workshop' => 'Workshop',
            ]),
            ImageField::new('image', 'Main Cover Image')
                ->setBasePath('uploads/activities/')
                ->setUploadDir('public/uploads/activities/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->hideOnIndex(),
            DateTimeField::new('eventDate', 'Event Date')->setFormat('yyyy-MM-dd HH:mm'),
            AssociationField::new('sdgs', 'Tag SDGs')->setFormTypeOptions(['by_reference' => false]),
            BooleanField::new('isActive', 'Live on Website?'),
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
}