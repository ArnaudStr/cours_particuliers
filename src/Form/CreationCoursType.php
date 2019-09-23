<?php

namespace App\Form;

use App\Entity\Activite;
use App\Form\CreneauType;
use App\Entity\Cours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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
                "label" => "Cours par Webcam",
                'required' => false,
            ])

            ->add('domicile', CheckboxType::class, [
                "label" => "Cours à domicile",
                'required' => false,

            ])

            ->add('chezEleve', CheckboxType::class, [
                "label" => "Cours chez le professeur",
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
