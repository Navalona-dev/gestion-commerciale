<?php

namespace App\Form;

use App\Entity\Facture;
use App\Entity\MethodePaiement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;

class MethodePaiementType extends AbstractType
{
    private $frenchTransformer ;

    public function __construct(FrenchToDateTimeTransformer $frenchTransformer) {
        $this->frenchTransformer = $frenchTransformer;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('espece', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('mVola', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('referenceMvola', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('nomMvola', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('airtelMoney', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('referenceAirtel', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('nomAirtel', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('orangeMoney', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('referenceOrange', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('nomOrange', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->add('dateMethodePaiement', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
        ;
        $builder->get('dateMethodePaiement')->addModelTransformer($this->frenchTransformer);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MethodePaiement::class,
        ]);
    }
}
