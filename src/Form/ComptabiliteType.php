<?php

namespace App\Form;

use App\Entity\Depense;
use App\Entity\Benefice;
use App\Entity\Fourchette;
use App\Entity\Comptabilite;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;

class ComptabiliteType extends AbstractType
{
    private $frenchTransformer ;

    public function __construct(FrenchToDateTimeTransformer $frenchTransformer) {
        $this->frenchTransformer = $frenchTransformer;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
            ->add('dateComptabilite', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
            ->add('fourchette', EntityType::class, [
                'class' => Fourchette::class,
                'choice_label' => function (Fourchette $fourchette) {
                    return sprintf(
                        'Entre %d et %d (%s)', 
                        $fourchette->getMinVal(), 
                        $fourchette->getMaxVal(), 
                        $fourchette->getStatus()
                    );
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                              ->orderBy('f.minVal', 'ASC'); // Tri par valeur minimum
                },
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off'
                ],
                'required' => true,
                'placeholder' => "Selectionner une fourchette"
            ])
        ;
        $builder->get('dateComptabilite')->addModelTransformer($this->frenchTransformer);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comptabilite::class,
        ]);
    }
}
