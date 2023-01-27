<?php

namespace App\Component\Product\Communication\Form;


use App\DTO\MainMenuDataTransferObject;
use App\Entity\MainCategorys;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NewMainCategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('mainCategoryName', TextType::class)
            ->add('displayName', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Product']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MainCategorys::class,
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'task_item',
        ]);
    }
}
