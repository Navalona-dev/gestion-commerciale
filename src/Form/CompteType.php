<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ]
                ]);
            if ($options['genre'] == 2) {
                $builder->add('code', TextType::class, [
                    'attr' => [
                        'class' => 'form-control form-control-md',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ]);
            }

            if ($options['genre'] == 1) {
                $builder
                ->add('indiceFacture', TextType::class, [
                    'attr' => [
                        'class' => 'form-control form-control-md',
                        'autocomplete' => 'off',
                    ],
                    'required' => true
                ])
                ->add('delaisPaiement', TextType::class, [
                    'attr' => [
                        'class' => 'form-control form-control-md',
                        'autocomplete' => 'off',
                    ],
                    'required' => false
                ])
                ;
            }
            
            $builder->remove('etat', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
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
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                'autocomplete' => 'off'

                ], 
                'required' => false,
            ])
        
            ->remove('nbAffaire', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3'
                ],
                'required' => false
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            
            ->remove('isLivraison', CheckboxType::class, [
                'required' => false,
                'label' => 'Livrason?'
            ])
            ->remove('numero', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md',
                    'autocomplete' => 'off',
                ],
                'required' => false
            ])
            ->remove('commentaire', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 ckeditor'
                ],
                'required' => false
            ])
            ->remove('ca', NumberType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'step' => '0.01',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->remove('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control form-control-md mb-3'
                ],
            ])
            ->remove('application', EntityType::class, [
                'class' => Application::class,
                'choice_label' => 'entreprise',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3'
                ]
            ])
            ->remove('compteApplications', EntityType::class, [
                'class' => Application::class,
                'choice_label' => 'entreprise',
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control form-control-md mb-3'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
            'genre' => 1
        ]);
    }
}
