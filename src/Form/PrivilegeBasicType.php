<?php

namespace App\Form;

use App\Entity\Privilege;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PrivilegeBasicType extends AbstractType
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
                ]);

            /*->add('description', CKEditorType::class, [
                'config' => [
                    'uiColor' => '#e2e2e2',
                    "extraPlugins" => "lineheight",
                    "line_height"=>"0.5;1;1.1;1.2;1.3;1.4;1.5",
                    'required' => false,

                ],
                'plugins' => ['lineheight' => [
                    'path' => '/bundles/fosckeditor/plugins/lineheight/', 
                    'filename' => "plugin.js"
                ]]
            ])*/
            //->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-fill btn-green mx-auto']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Privilege::class,
            'isEdit' => false,
        ]);
    }

    public function getParent()
    {
        return PrivilegeType::class;
    }
}