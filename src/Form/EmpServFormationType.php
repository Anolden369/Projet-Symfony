<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Entity\Formation;

class EmpServFormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom formation']
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Sélectionnez une date',
                    'style' => 'padding: 8px; border: 1px solid #ced4da; border-radius: 4px; width: 100%; box-sizing: border-box; font-size: 16px;'
                ]
            ])
            ->add('nbreHeures')
            ->add('departement', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Departement']
            ])
            ->add('ajouter', SubmitType::class, [
                'label' => "Ajouter",
                'attr' => ['class' => 'btn btn-primary w-100 fs-4 mb-4 rounded-2']
            ])
            ->add('produit', EntityType::class, [
                'class' => 'App\Entity\Produit',
                'choice_label' => 'libelle',
            ])                      
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
