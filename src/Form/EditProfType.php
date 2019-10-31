<?php

namespace App\Form;

use App\Entity\Prof;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
                "required" => false              
            ])
            ->add('description',TextType::class, [
                "required" => false              
            ])

            ->add('pictureFilename', FileType::class, [
                'attr' =>[
                    'multiple' => 'multiple',
                    'id' => 'preview',
                    'onmousedown' => 'return false',
                    'placeholder' => 'Selectionnez une image',
                    'onkeydown' => 'return false'
                ],
                'label' => 'Modifier image',
                'required' => false,
                'data_class' => null,
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
