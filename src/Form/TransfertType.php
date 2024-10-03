<?php

namespace App\Form;

use App\Entity\Transfert;
use App\Entity\Application;
use Doctrine\ORM\EntityRepository;
use App\Service\ApplicationManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class TransfertType extends AbstractType
{
    private $application;

    public function __construct(
        ApplicationManager $applicationManager
    )
    {
        $this->application = $applicationManager->getApplicationActive();
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('application', EntityType::class, [
                'class' => Application::class,
                'attr' => [
                    'class' => 'form-control form-control-md chosen-select mb-3'
                ],
                'placeholder' => 'selectionner une application',
                'choice_label' => 'entreprise',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->andWhere('a != :currentApplication')
                        ->setParameter('currentApplication', $this->application);
                },
            ])
            ->add('quantity', NumberType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transfert::class,
            
        ]);
    }
}
