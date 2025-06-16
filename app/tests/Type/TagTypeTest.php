<?php

/**
 * Class TagTypeTest.
 *
 * This class provides unit tests for TagType.
 */

namespace App\Tests\Form\Type;

use App\Entity\Tag;
use App\Form\Type\TagType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagTypeTest.
 *
 * Unit tests for the TagType form class.
 */
class TagTypeTest extends TestCase
{
    /**
     * Tests the buildForm method.
     *
     * @return void
     */
    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $formBuilder->method('add')->willReturn($formBuilder);

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

        $tagType = new TagType();
        $tagType->buildForm($formBuilder, []);
    }

    /**
     * Tests the configureOptions method.
     *
     * @return void
     */
    public function testConfigureOptions(): void
    {
        $optionsResolver = $this->createMock(OptionsResolver::class);

        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->callback(function ($defaults) {
                return isset($defaults['data_class']) && $defaults['data_class'] === Tag::class;
            }));

        $tagType = new TagType();
        $tagType->configureOptions($optionsResolver);
    }

    /**
     * Tests the getBlockPrefix method.
     *
     * @return void
     */
    public function testGetBlockPrefix(): void
    {
        $tagType = new TagType();
        $this->assertEquals('tag', $tagType->getBlockPrefix());
    }
}