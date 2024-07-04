<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Application;
use App\Entity\ProductImage;
use App\Form\ProduitImageType;
use App\Entity\ProduitCategorie;
use App\Entity\ProduitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProduitCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                ],
                'required' => false
            ])
            ->add('reference', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('prixHt', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('tva', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('qtt', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('stockRestant', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('stockMin', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('stockMax', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('uniteVenteGros', ChoiceType::class, [
                'choices' => array_flip(ProduitCategorie::uniteVenteGros),
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'required' => false
            ])
            ->add('uniteVenteDetail', ChoiceType::class, [
                'choices' => array_flip(ProduitCategorie::uniteVenteDetails),
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'required' => false
            ])
            ->add('prixVenteGros', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('prixVenteDetail', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('presentationDetail', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('presentationGros', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('prixTTC', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('prixAchat', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            /*->add('application', EntityType::class, [
                'class' => Application::class,
                'choice_label' => 'entreprise',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'required' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.isActive = true'); 
                }
            ])*/
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'required' => false
            ])
            ->add('type', EntityType::class, [
                'class' => ProduitType::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'required' => false
            ])
            ->add('productImages', CollectionType::class, [
                'entry_type' => ProduitImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'class' => 'mb-3'
                ],
                'required' => false
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProduitCategorie::class,
        ]);
    }
}
