<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off',
                    'placeholder' => 'Saisissez votre adresse e-mail'
                ],
                'required' => true
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'placeholder' => 'Saisissez votre mot de passe'
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
