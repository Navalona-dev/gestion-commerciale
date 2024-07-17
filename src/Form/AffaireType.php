<?php

namespace App\Form;

use App\Entity\Affaire;
use App\Entity\Gomyclic\Compte;
use App\Entity\Gomyclic\Formation\FormationOf;
use App\Entity\Gomyclic\UtilisateurCollaborateur;
use App\Form\Gomyclic\ProduitGomyClicType;
use App\Service\ApplicationManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Gomyclic\Application;

class AffaireType extends AbstractType
{
    public function __construct(ApplicationManager $applicationManager)
    {
        $this->application = $applicationManager->getApplicationActive();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control form-control-md mb-3',
                    'autocomplete' => 'off'
                ]
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Affaire::class
        ]);
    }
}
