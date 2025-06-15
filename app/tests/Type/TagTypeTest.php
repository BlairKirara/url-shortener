<?php

namespace App\Tests\Form\Type;

use App\Entity\Tag;
use App\Form\Type\TagType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TagTypeTest class.
 *
 * Tests for the TagType form class.
 */
class TagTypeTest extends TestCase
{
    /**
     * Test buildForm method.
     */
    public function testBuildForm(): void
    {
        // Create a mock for the FormBuilderInterface
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        // Setup the form builder to return itself after add() for method chaining
        $formBuilder->method('add')->willReturn($formBuilder);

        // Define expectations for the add method call
        $formBuilder->expects($this->once())
            ->method('add')
            ->with(
                'name',
                TextType::class,
                $this->callback(function ($options) {
                    return isset($options['label'])
                        && $options['label'] === 'label.name'
                        && isset($options['required'])
                        && $options['required'] === true
                        && isset($options['attr']['max_length'])
                        && $options['attr']['max_length'] === 64;
                })
            );

        // Create an instance of the TagType class
        $tagType = new TagType();

        // Call the buildForm method
        $tagType->buildForm($formBuilder, []);
    }

    /**
     * Test configureOptions method.
     */
    public function testConfigureOptions(): void
    {
        // Create a mock for the OptionsResolver
        $optionsResolver = $this->createMock(OptionsResolver::class);

        // Expect setDefaults to be called with the correct data_class
        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->callback(function ($defaults) {
                return isset($defaults['data_class']) && $defaults['data_class'] === Tag::class;
            }));

        // Create an instance of the TagType class
        $tagType = new TagType();

        // Call the configureOptions method
        $tagType->configureOptions($optionsResolver);
    }

    /**
     * Test getBlockPrefix method.
     */
    public function testGetBlockPrefix(): void
    {
        // Create an instance of the TagType class
        $tagType = new TagType();

        // Test that the getBlockPrefix method returns the expected value
        $this->assertEquals('tag', $tagType->getBlockPrefix());
    }
}