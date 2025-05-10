<?php

/**
 * Url block type.
 */

namespace App\Form\Type;

use App\Entity\Url;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * Class UrlBlockType.
 *
 * This class represents the form type for blocking a URL.
 */
class UrlBlockType extends AbstractType
{
    /**
     * Build the URL block form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blockTime', DateTimeType::class, [
            'input' => 'datetime_immutable',
            'label' => 'label.block_time',
            'widget' => 'choice',
            'years' => range(date('Y'), date('Y') + 5),
            'data' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Url::class]);
    }

    /**
     * Get the block prefix.
     *
     * @return string The block prefix
     */
    public function getBlockPrefix(): string
    {
        return 'BlockUrl';
    }
}
