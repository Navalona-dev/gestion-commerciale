<?php

namespace App\Form;

use App\Entity\Privilege;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Permission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PrivilegeAssignationPermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            /*->add('permissions', CollectionType::class, [
                'entry_type' => PermissionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'prototype' => false,
            ])*/
            ->add('permissions', EntityType::class, [
                'class' => Permission::class,
                'choices' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                    ->groupBy('p.categoryofpermission', 'ASC');
                     },
                'choice_label' => 'title',
            ])
          /*  ->add('permissions', ChoiceType::class, [
                'choices' => [
                    'Ministeriel' => 'Ministeriel',
                    'Regionale' => 'Regionale',
                    'Technique' => 'Technique',
                    'Communale' => 'Communale',
                    'Administrateur' => 'Administrateur',
                ],
                'expanded' => false,
                'required' => true,

                'label' => 'Spécifiez le niveau du privilège:',
            ])*/
            //->add('permissions')
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-fill btn-green mx-auto']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Privilege::class,
        ]);
    }

    public function getParent()
    {
        return PrivilegeType::class;
    }
}
