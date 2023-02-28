<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'Username',
                'attr' => [
                    'placeholder' => 'Email or phone number',
                ],
            ])
            ->add('reset_code', null, [
                'label' => 'Reset code',
                'attr' => [
                    'placeholder' => 'Introduce the reset code',
                ],
            ])
            ->add('plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'first_options'  =>
                        [
                            'label' => false,
                            'attr'=>['autocomplete' => 'off', 'placeholder'=>'Your new password'],
                    ],
                    'second_options' =>
                        [
                            'label' => false,
                            'help'=>'Password must be at least 6 characters long',
                            'attr'=>['autocomplete' => 'off',
                                'placeholder'=>'Repeat your new password',
                        ],
                    'required'=>false,
                    'constraints'=>[
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 255,
                        ]),
                    ],
                ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
