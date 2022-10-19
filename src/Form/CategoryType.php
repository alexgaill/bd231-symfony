<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => "Nom de la catégorie",
            'attr' => [
                'placeholder' => "Nom de la catégorie"
            ],
            'row_attr' => [
                'class' => 'mb-3 form-floating'
            ]
        ])
        ->add('picture', FileType::class, [
            'label' => "Image de mise en avant (.png, .jpeg)",
            'required' => false,
            'mapped' => false,
            'constraints' => [
                new File(
                    mimeTypes:["image/jpeg", "image/png"],
                    mimeTypesMessage:"L'image doit être au format jpeg ou png"
                )
            ]
        ])
        ->add('button', SubmitType::class, [
            'label' => "Modifier"
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
