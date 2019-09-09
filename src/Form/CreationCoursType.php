<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CreationCoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("activite", EntityType::class, [
                "class"=>Activite::class, 
                "choice_label" => 'nom',
                "mapped"=>false
            ])

            ->add("tarifHoraire", IntegerType::class, [
                "label" => "Tarif horaire",
                'attr' => [
                    "min" => 1,
                ],
                "mapped"=>false
            ])

            ->add('dateDebut', DateTimeType::class, [
                "label"=>"Date de de début",
                "format"=>"HHddMMMMyyyy",
                "mapped"=>false
            ])

            ->add('dateFin', DateTimeType::class, [
                "label"=>"Date de fin",
                "format"=>"HHddMMMMyyyy",
                "mapped"=>false
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])        
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
