<?php

namespace App\Form;

use App\Entity\Eleve;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class EditEleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('nom',TextType::class, [
                'label' => 'Nom *'
            ])
            ->add('prenom',TextType::class, [
                'label' => 'Prenom *'
            ])

            ->add('pictureFilename', FileType::class, [
                'attr' =>[
                    'multiple' => 'multiple',
                    'id' => 'preview',
                    'onmousedown' => 'return false',
                    'placeholder' => 'Selectionnez une image',
                    'onkeydown' => 'return false'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '4096k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'maxSizeMessage' => 'Image trop lourde',
                        'mimeTypesMessage' => 'Image non valide, formats acceptés : jpeg, jpg & png',
                    ])
                ],
                
                // 'label' => 'Modifier image',
                'required' => false,
                'mapped' => false
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Eleve::class,
        ]);
    }
}
