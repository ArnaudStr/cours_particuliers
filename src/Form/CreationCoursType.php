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
            ])

            ->add("tarifHoraire", IntegerType::class, [
                "label" => "Tarif horaire",
                'attr' => [
                    "min" => 1,
                ],
            ])

            ->add('webcam', CheckboxType::class, [
                "label" => "Téléprésentiel",
                'required' => false,
            ])

            ->add('chezProf', CheckboxType::class, [
                "label" => "Cours chez le professeur",
                'required' => false,

            ])

            ->add('chezEleve', CheckboxType::class, [
                "label" => "Cours chez l'élève",
                'required' => false,

            ])

            ->add('description', TextareaType::class, [
            ])

            ->add('niveaux', TextareaType::class, [
                'label' => 'Veuillez renseigner les niveaux pour lesquels vous voulez enseigner',
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
