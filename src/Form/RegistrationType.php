<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',EmailType::class,[
                'label' => 'Adresse email',
                "mapped" => false,
                'label' => 'Email',
            ])
  
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques',
                'required' => true,
                'mapped' => false,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
                'attr' => [
                    'class' => 'uk-input'
                ],
                
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères',
                        'max' => 32,
                        'maxMessage' => 'Le mot de passe doit contenir au plus 32 caractères'
                    ])
                ],
            ])

            ->add('nom',TextType::class, [
                "mapped" => false,
                'label' => 'Nom',

            ])
            ->add('prenom',TextType::class, [
                "mapped" => false,
                'label' => 'Prenom'
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
        ;
    }
}
