<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CreationCoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("activite", EntityType::class, [
                "class"=>Activite::class, 
                "choice_label" => 'nom',
                'label' => 'Choisissez l\'activité *'
            ])

            ->add("tarifHoraire", IntegerType::class, [
                "label" => "Tarif horaire *",
                'attr' => [
                    "min" => 1,
                    'minMessage' => 'Veuillez inscrire un prix supérieur à 0'

                ],
            ])

            ->add('webcam', CheckboxType::class, [
                "label" => "Téléprésentiel",
                'required' => false,
            ])

            ->add('chezProf', CheckboxType::class, [
                "label" => "Cours chez vous",
                'required' => false,

            ])

            ->add('chezEleve', CheckboxType::class, [
                "label" => "Déplacement chez l'élève",
                'required' => false,

            ])

            ->add('description', TextareaType::class, [
                "label" => "Description du cours *",
            ])

            ->add('niveaux', TextType::class, [
                'label' => 'Niveaux pour lesquels vous voulez enseigner ce cours',
                'required' => false,
                ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])        
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
