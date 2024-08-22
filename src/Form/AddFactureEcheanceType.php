<?php

namespace App\Form;

use App\Entity\Facture;
use App\Form\FactureEcheanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AddFactureEcheanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('factureEcheances', CollectionType::class, [
                'entry_type' => FactureEcheanceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'class' => 'mb-3'
                ]
          ])
          ->add('reglement', TextType::class, [
            'attr' => [
                'class' => 'form-control form-control-md',
                'autocomplete' => 'off',
            ],
            'required' => false
          ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }
}
