<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'mapped' => false
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
