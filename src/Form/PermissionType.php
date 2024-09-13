<?php

namespace App\Form;

use App\Entity\Permission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Categoryofpermission;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'attr' => array(
                    'readonly' => (($options['isEdit']) ? true : false),
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ),
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'ckeditor form-control form-control-md mb-3'
                ],
                'required' => true
            ])
            ->add('categoryofpermission', EntityType::class, [
                'class' => Categoryofpermission::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
                'choice_label' => 'title',
                'required' => true,
                'placeholder' => 'Choisir categorie',
                'attr' => [
                    'class' => 'form-control form-control-md mb-3 chosen-select'
                ]
            ])

            ->remove('privileges')
            //->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-fill btn-green mx-auto']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
            'isEdit' => false,
        ]);
    }
}
