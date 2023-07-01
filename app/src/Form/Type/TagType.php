<?php
/**
 * Tag type.
 */

namespace App\Form\Type;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagType.
 *
 * This class represents the form type for the Tag entity.
 */
class TagType extends AbstractType
{
    /**
     * Build the Tag form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'label.name',
                'required' => true,
                'attr' => ['max_length' => 64],
            ]
        );
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }

    /**
     * Get the block prefix.
     *
     * @return string The block prefix
     */
    public function getBlockPrefix(): string
    {
        return 'tag';
    }
}
