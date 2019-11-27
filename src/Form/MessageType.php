<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'mapped' => false,
                new Length([
                    'max' => 1000,
                    'maxMessage' => 'Le message doit contenir au plus 1000 caractères'
                ])
            ])
            
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }

    // public function configureOptions(OptionsResolver $resolver)
    // {
    //     $resolver->setDefaults([
    //         'data_class' => Message::class,
    //     ]);
    // }
}
