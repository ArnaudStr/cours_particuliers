<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',EmailType::class,[
                'attr' => [
                    'placeholder' => 'Email',
                ],
                "mapped" => false,
                'label' => false,
            ])
  
            ->add('plainPassword', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Mot de passe',
                ],
                'mapped' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins 6 caractères !',
                        'max' => 4096,
                    ]),
                ],
                'label' => false
            ])
            ->add('nom',TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom',
                ],
                "mapped" => false,
                'label' => false

            ])
            ->add('prenom',TextType::class, [
                'attr' => [
                    'placeholder' => 'Prenom',
                ],
                "mapped" => false,
                'label' => false

            ])
            
            // ->add('adresse',TextType::class, [
            //     'attr' => [
            //         'placeholder' => 'Adresse',
            //     ],
            //     "mapped" => false,
            //     'label' => false
            // ])



            ->add('isEleve', ChoiceType::class, [
                'attr' => [
                    'placeholder' => 'email',
                ],
                "choices" => [
                    'Eleve' => true,
                    'Prof' => false
                ],
                "mapped" => false,
                'label' => false,
                'expanded' => true,
                'multiple' => false,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }
}
