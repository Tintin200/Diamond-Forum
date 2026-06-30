<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sujet', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr' => [
                    'placeholder' => 'Entrez le titre de l\'article',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer un titre.',
                    ),
                ],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu de l\'article',
                'attr' => [
                    'placeholder' => 'Rédigez votre article ici...',
                    'rows' => 8,
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer le contenu de l\'article.',
                    ),
                ],
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags / Catégories',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Transfert, MLB, Yankees (séparés par des virgules)',
                    'class' => 'form-control'
                ],
            ])
            ->add('image', TextType::class, [
                'label' => 'URL de l\'image de couverture (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://exemple.com/image.jpg',
                    'class' => 'form-control'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
