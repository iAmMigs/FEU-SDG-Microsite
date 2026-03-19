<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Sdg;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'w-full rounded-lg border-gray-300 focus:border-feu-green-700']
            ])
            ->add('category', TextType::class)
            ->add('content', TextareaType::class, [
                // We add a specific class to target with our JS Rich Text Editor
                'attr' => ['class' => 'rich-text-editor min-h-[400px]']
            ])
            // File upload logic for the main cover image remains the same
            ->add('image') 
            ->add('event_date', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('sdgs', EntityType::class, [
                'class' => Sdg::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, // Renders as checkboxes
                'label' => 'Tag SDGs'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Set as Active / Visible',
                'required' => false,
            ])
            ->add('publishAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Schedule Publication Date (Leave blank to publish immediately)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}