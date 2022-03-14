<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;

class MovieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [new Length(['min' => 3, 'max' => 150])]
            ])
            ->add('rating', IntegerType::class, [
                'required' => false,
                'constraints' => new Positive(),
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => new Image([
                    'maxSize' => "10M",
                    'minWidth' => 200,
                    'maxWidth' => 5000,
                    'minHeight' => 200,
                    'maxHeight' => 5000,
                    'mimeTypes' => [
                        "image/jpeg",
                        "image/jpg",
                        "image/png",
                        "image/gif",
                    ],
                ]),
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
