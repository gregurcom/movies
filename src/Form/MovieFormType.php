<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Movie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

final class MovieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [new Length(['min' => 3, 'max' => 150])],
                'label' => 'form.labels.title',
            ])
            ->add('rating', IntegerType::class, [
                'required' => false,
                'constraints' => new Positive(),
                'label' => 'form.labels.rating',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
                'choice_label' => 'title',
                'label' => 'form.labels.category',
            ])
            ->add('actors', EntityType::class, [
                'class' => Actor::class,
                'choice_label' => 'name',
                'multiple' => true,
                'label' => 'form.labels.actors',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => new Length(['min' => 2]),
                'label' => 'form.labels.description',
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
                'label' => 'form.labels.image',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.buttons.submit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
