<?php

namespace App\Form;

use App\Entity\Creneau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CreneauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('jour',ChoiceType::class, [
                "label" => 'Module:',
                'choices' => [  'Lundi' => "monday",
                                'Mardi' => "tuesday",
                                'Mercredi' => "wednesday",
                                'Jeudi' => "thursday",
                                'Vendredi' => "friday",
                                'Samedi' => "saturday",
                                'Dimanche' => "sunday",
                            ],
            ])

            ->add('heureDebut',TimeType::class, [
                "label" => 'Heure de début :',
            ])

            ->add('heureFin', TimeType::class, [
                "label" => "Heure de fin :",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Creneau::class,
        ]);
    }
}
