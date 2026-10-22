<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * RegistrationFormType - Formulaire pour l'inscription des utilisateurs.
 * Champs : email, agreeTerms, plainPassword (non mappés à l'entité).
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Construit le formulaire avec les champs : email, agreeTerms, plainPassword.
     * @param FormBuilderInterface $builder Builder du formulaire
     * @param array $options Options du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter nos conditions d\'utilisation.',
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // au lieu d'être défini directement sur l'objet,
                // ce champ est lu et encodé dans le controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer un mot de passe.',
                    ),
                    new Length(
                        min: 12,
                        minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères pour des raisons de securite.',
                        // longueur max autorisée par Symfony pour des raisons de sécurité
                        max: 4096,
                    ),
                ],
            ])
        ;
    }

    /**
     * Configure les options du formulaire. Définit data_class : User::class.
     * @param OptionsResolver $resolver Résolveur d'options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
