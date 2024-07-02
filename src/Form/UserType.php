<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Privilege;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Niveauhierarchique;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    private $ManagerRegistry;
    private $choicesRegions = [];
    private $choicesCommunes = [];
    public function __construct(ManagerRegistry $ManagerRegistry)
    {
        $this->ManagerRegistry = $ManagerRegistry;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
           
            ->add('email', EmailType::class, [
                'label' => 'Email', 
                'attr' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => '',
                'autocomplete' => 'off'
            ]])
            
            ->add('civilite', ChoiceType::class, [
                'choices' => array_flip(User::CIVILITE),
                'attr' => [
                    'class' => 'form-control chosen-select',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'entrer une civilité',
                    ])
                    ],
                    'required' => true
            ])
            ->add('nom', TextType::class, ['label' => 'Nom', 'attr' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => '',
                'autocomplete' => 'off'
        ]])
            ->add('prenom', TextType::class, ['label' => 'Prenom', 'attr' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => '',
                'autocomplete' => 'off'
        ]])
            /*->add('username', TextType::class, ['label' => 'Username', 'attr' => [
                'required' => true,
                'class' => 'form-control',
                'placeholder' => ''
            ]])*/
            ->add('telephone', TextType::class, ['label' => 'Email', 'attr' => [
                'required' => false,
                'class' => 'form-control',
                'placeholder' => '',
                'autocomplete' => 'off'
        ]]);
        /*if ($options['isUsernameAlreadyExist']) {
            $builder->add('isUsernameAlreadyExist', HiddenType::class, [
                "mapped" => false,
                'data' => $options['isUsernameAlreadyExist'],
            ]);
        }*/
        if ($options['isEmailAlreadyExist']) {
            
            $builder->add('isEmailAlreadyExist', HiddenType::class, [
                "mapped" => false,
                'data' => $options['isEmailAlreadyExist'],
            ]);
        }
        if (!$options['isProfile']) {
            $builder->add('applications', EntityType::class, [
                'class' => Application::class,
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.entreprise', 'ASC');
                },
                'choice_label' => 'entreprise',
                'multiple' => true,
                'required' => false,
                'placeholder' => 'Choisir point de vente',
                'attr'=> ['class' => 'form-control chosen-select']
            ])
            ->add('privileges', EntityType::class, [
                'class' => Privilege::class,
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.title', 'ASC');
                },
                'choice_label' => 'title',
                'multiple' => true,
                'required' => false,
                'placeholder' => 'Choisir privilege',
                'attr'=> ['class' => 'form-control chosen-select']
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Activé',
                'required' => false,
            ]);
        }
        
        if ($options['isProfile']) {
            $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'text password']],
                'required' => false,
                'empty_data'=> '',
                'first_options'  => ['label' => '', 'attr' => ['class' => 'form-control', 'placeholder' => '']],
                'second_options' => ['label' => '', 'attr' => ['class' => 'form-control', 'placeholder' => '']],
            ]);
        }
        $builder->add('imageFile', VichImageType::class, [
                'download_uri' => false,
                'required' => false,
                'download_label' => 'Voir',
                'image_uri' => false,
                'allow_delete' => false,
                'asset_helper' => true,
                'attr' => [
                    'class' => 'form-control form-control-md mb-3'
                ]
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isProfile' => false,
            'isEdit' => false,
            'isEmailAlreadyExist' => false,
            'isUsernameAlreadyExist' => false,
        ]);
    }
}
