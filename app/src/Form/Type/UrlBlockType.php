<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Url;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class UrlBlockType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blockTime', DateTimeType::class, [
            'input' => 'datetime_immutable',
            'label' => 'label.block_time',
            'widget' => 'choice',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ],
            'years' => range(date('Y'), date('Y') + 10),
            'data' => new \DateTimeImmutable(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Url::class]);
    }


    public function getBlockPrefix(): string
    {
        return 'BlockUrl';
    }
}
