<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface<string> $builder
     * @param array<string> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'email']
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_name' => 'password',
                'second_name' => 'confirm_password',
                'invalid_message' => 'user.password.repeatedType',
                'first_options'  => ['label' => false,],
                'second_options' => ['label' => false,],
                'constraints' => [
                    new NotBlank(['message' => 'user.password.not_blank']),
                    new Length(['min' => 8, 'minMessage' => 'user.password.min_length']),
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', 'user.password.regex'),
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'gender.sexe_male' => 'male',
                    'gender.sexe_female' => 'female',
                ],
                'data' => 'male',
                'expanded' => true,
                'multiple' => false,
                'label' => false
            ])
            ->add('alias', TextType::class, [
                'label' => false
            ])
            ->add('captcha', HiddenType::class, [
                'mapped' => false,
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
