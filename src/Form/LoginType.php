<?php

namespace App\Form;

use App\Entity\Employe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as SFType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('login', TextType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'Login']
        ])
        ->add('mdp', PasswordType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'Mot de passe']
        ])
        ->add('Identifier', SubmitType::class, [
            'label' => "S'identifier",
            'attr' => ['class' => 'btn btn-primary w-100 fs-4 mb-4 rounded-2']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
        ]);
    }
}