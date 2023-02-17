<?php

namespace App\Component\Product\Communication\Form;

use App\DTO\ProductDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProductCreateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('category', TextType::class)
            ->add('productName', TextType::class)
            ->add('description', TextType::class)
            ->add('price', TextType::class)
            ->add('attributes',  TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Product']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductsDataTransferObject::class,
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'task_item',
        ]);
    }
}
