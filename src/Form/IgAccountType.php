<?php

namespace App\Form;

use App\Entity\IgAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IgAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nazwa konta na IG',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Hasło do konta na IG',
            ])
            ->add('linkedAccount', TextType::class, [
                'label' => 'Konto z którego będą pobierani obserwujący',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Zapisz',
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IgAccount::class,
        ]);
    }
}