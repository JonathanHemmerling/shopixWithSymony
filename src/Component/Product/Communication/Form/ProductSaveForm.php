<?php

namespace App\Component\Product\Communication\Form;

use App\DTO\ProductsDataTransferObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProductSaveForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*->add('attributes', ChoiceType::class, ['choices' => [$options['data']->attributes]])
            ->get('attributes')->addModelTransformer(
                new CallbackTransformer(
                    function ($attributesAsArray) {
                        return implode(', ', $attributesAsArray);
                    },
                    function ($attributesAsString) {
                        return explode(', ', $attributesAsString);
                    }
                )
            )*/
                ->add('attributes', TextType::class)
            ->add('category', TextType::class)
            ->add('articleNumber', TextType::class)
            ->add('productName', TextType::class)
            ->add('price', TextType::class)
            ->add('description', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductsDataTransferObject::class,
            'compound' => true,
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'task_item',

        ]);
    }
}
