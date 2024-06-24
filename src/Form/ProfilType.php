<?php

namespace App\Form;

use App\Entity\Gomyclic\Utilisateur;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('civilite', ChoiceType::class, [
                'choices' => array_flip(User::CIVILITE),
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'entrer une civilité',
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ]
            ])
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => array(
                    'class' => 'form-control form-control-md mb-3',
                ),
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
                'attr' => array(
                    'class' => 'form-control form-control-md mb-3',
                ),
            ])
            ->add('telephone', TextType::class, [
                'required' => true,
                'attr' => array(
                    'class' => 'form-control form-control-md mb-3',
                ),
            ])
            ->add('imageFile', VichImageType::class, [
                'download_uri' => false,
                'required' => false,
                'download_label' => 'Voir',
                'image_uri' => false,
                'allow_delete' => true,
                'asset_helper' => true,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
