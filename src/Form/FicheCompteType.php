<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Application;
use App\Entity\ProduitCategorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FicheCompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => array_flip(Compte::STATUT),
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
        ]);
    }
}
