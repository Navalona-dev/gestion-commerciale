<?php

namespace App\Form;

use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\ProduitType;
use App\Form\ProduitImageType;
use App\Entity\ProduitCategorie;
use App\Service\ApplicationManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class UpdateProduitCategorieType extends AbstractType
{
    private $application;
    public function __construct(ApplicationManager $applicationManager)
    {
        $this->application = $applicationManager->getApplicationActive();
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $application = $this->application;
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
                    'class' => 'form-control form-control-md mb-3 ckeditor',
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
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'query_builder' => function(EntityRepository $er)  use ($application) {
                    return $er->createQueryBuilder('c')
                    ->andWhere('c.application = :application')
                    ->setParameter('application', $application)
                    ->orderBy('c.nom', 'ASC');
                },
                'required' => false
            ])
            ->add('type', EntityType::class, [
                'class' => ProduitType::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ],
                'query_builder' => function(EntityRepository $er)  use ($application) {
                    return $er->createQueryBuilder('c')
                    ->andWhere('c.application = :application')
                    ->setParameter('application', $application)
                    ->orderBy('c.nom', 'ASC');
                },
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
            ->add('volumeGros', NumberType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('volumeDetail', NumberType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->remove('comptes', EntityType::class, [
                'class' => Compte::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.genre = :genre')
                        ->setParameter('genre', 2)
                        ->orderBy('c.nom', 'ASC'); 
                },
                'choice_label' => function (Compte $compte) {
                    return $compte->getNom();
                },
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
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