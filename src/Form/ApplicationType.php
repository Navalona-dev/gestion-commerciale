<?php

namespace App\Form;

use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Categoryofapplication;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entreprise', TextType::class, [
                'required' => true,
                'attr' => array(
                    'readonly' => false,
                ),
            ])
            ->add('nomResp', TextType::class, [
                'required' => false,
                'attr' => array(
                    'readonly' => false,
                ),
            ])
            ->add('prenomResp', TextType::class, [
                'required' => false,
                'attr' => array(
                    'readonly' => false,
                ),
            ])
            ->add('mailResp', TextType::class, [
                'required' => false,
                'attr' => array(
                    'readonly' => false,
                ),
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'attr' => array(
                    'readonly' => false,
                ),
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'attr' => array(
                    'readonly' => false,
                ),
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Activé',
                'required' => false,
            ])
            //->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-fill btn-green mx-auto']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Application::class
        ]);
    }
}
