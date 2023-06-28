<?php
/*
 * Url Block Type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Url;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * Class UrlBlockType.
 */
class UrlBlockType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface<mixed> $builder The form builder
     * @param array<string, mixed>        $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blockExpiration', DateTimeType::class, [
            'input' => 'datetime_immutable',
            'label' => 'label.block_expiration',
            'widget' => 'choice',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ],
            'years' => range(date('Y'), date('Y') + 10),
            'data' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver<mixed> $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Url::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'BlockUrl';
    }
}
