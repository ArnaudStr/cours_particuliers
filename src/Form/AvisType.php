<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire *',
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Le commentaire doit contenir au plus 1000 caractères'
                    ])
                ],

            ])
            ->add('note', IntegerType::class, [
                'label' => 'Note * ( /5)',
                'attr' => [
                    'min' => 0,
                    'max' => 5
                ],
                
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}

