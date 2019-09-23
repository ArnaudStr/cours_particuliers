<?php

namespace App\Form;

use App\Entity\Prof;
use App\Form\CreneauType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;



class EditProfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('nom',TextType::class, [
            ])
            ->add('prenom',TextType::class, [
            ])
            ->add('adresse',TextType::class, [
            ])
            ->add('description',TextType::class, [
                "required" => false              
            ])

            ->add('pictureFilename', FileType::class, [
                'attr' =>[
                    'multiple' => 'multiple',
                    'id' => 'preview',
                    'onmousedown' => 'return false',
                    'placeholder' => 'test',
                    'title' => 'testTitre',
                    'onkeydown' => 'return false'
                ],
                'label' => 'Modifier image',
                'required' => false,
                'data_class' => null,
            ])

            ->add('creneaux', CollectionType::class, [
                'entry_type' => CreneauType::class,
                'entry_options' => ['label' => "Selectionnez un creneau :", ],
                'allow_add' => true,
                'allow_delete' => true,
                "by_reference" => false,
                'label' => false,
            ])
            
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Prof::class,
        ]);
    }
}
