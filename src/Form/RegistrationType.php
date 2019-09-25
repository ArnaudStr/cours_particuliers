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
                "mapped" => false
            ])
  
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins 6 caractères !',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('nom',TextType::class, [
                "mapped" => false
            ])
            ->add('prenom',TextType::class, [
                "mapped" => false
            ])
            ->add('adresse',TextType::class, [
                "mapped" => false
            ])

            ->add('isEleve', ChoiceType::class, [
                "choices" => [
                    'Eleve' => true,
                    'Prof' => false
                ],
                "mapped" => false
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }
}
