<?php

namespace App\Form;

use App\Entity\Transfert;
use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class TransfertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('application', EntityType::class, [
                'class' => Application::class,
                'attr' => [
                    'class' => 'form-control form-control-md chosen-select mb-3'
                ],
                'choice_label' => 'entreprise'
            ])
            ->add('quantity', NumberType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transfert::class,
            
        ]);
    }
}
