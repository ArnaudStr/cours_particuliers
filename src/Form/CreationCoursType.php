<?php

namespace App\Form;

use App\Entity\Activite;
use App\Form\CreneauType;
use App\Entity\CreneauCours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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

            ->add('creneaux', CollectionType::class, [
                'entry_type' => CreneauType::class,
                'entry_options' => ['label' => "Selectionnez un creneau :", ],
                'allow_add' => true,
                'allow_delete' => true,
                "by_reference" => false,
                'label' => false,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])        
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreneauCours::class,
        ]);
    }
}
