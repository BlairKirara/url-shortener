<?php

namespace App\Tests\Type;

use App\Entity\Url;
use App\Form\Type\UrlBlockType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UrlBlockTypeTest.
 *
 * Tests for the UrlBlockType form class.
 */
class UrlBlockTypeTest extends TestCase
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
                'blockTime',
                DateTimeType::class,
                $this->callback(function ($options) {
                    // Check for required options
                    $hasCorrectInput = isset($options['input']) && $options['input'] === 'datetime_immutable';
                    $hasCorrectLabel = isset($options['label']) && $options['label'] === 'label.block_time';
                    $hasCorrectWidget = isset($options['widget']) && $options['widget'] === 'choice';
                    $hasYears = isset($options['years']) && is_array($options['years']);
                    $hasData = isset($options['data']) && $options['data'] instanceof \DateTimeImmutable;

                    // Check that years range includes current year and 5 years ahead
                    $expectedYears = range(date('Y'), date('Y') + 5);
                    $correctYearsRange = $hasYears && $options['years'] === $expectedYears;

                    return $hasCorrectInput && $hasCorrectLabel && $hasCorrectWidget &&
                        $correctYearsRange && $hasData;
                })
            );

        // Create an instance of the UrlBlockType class
        $urlBlockType = new UrlBlockType();

        // Call the buildForm method
        $urlBlockType->buildForm($formBuilder, []);
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
            ->with(['data_class' => Url::class]);

        // Create an instance of the UrlBlockType class
        $urlBlockType = new UrlBlockType();

        // Call the configureOptions method
        $urlBlockType->configureOptions($optionsResolver);
    }

    /**
     * Test getBlockPrefix method.
     */
    public function testGetBlockPrefix(): void
    {
        // Create an instance of the UrlBlockType class
        $urlBlockType = new UrlBlockType();

        // Test that the getBlockPrefix method returns the expected value
        $this->assertEquals('BlockUrl', $urlBlockType->getBlockPrefix());
    }

    /**
     * Test the default DateTimeImmutable value.
     */
    public function testDefaultDateTimeImmutable(): void
    {
        // Capture the DateTimeImmutable value set in the form
        $capturedData = null;

        // Create a mock for the FormBuilderInterface
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        // Setup method to capture the data option
        $formBuilder->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use ($formBuilder, &$capturedData) {
                if ($fieldName === 'blockTime' && isset($options['data'])) {
                    $capturedData = $options['data'];
                }
                return $formBuilder;
            });

        // Create an instance of the UrlBlockType class
        $urlBlockType = new UrlBlockType();

        // Get the current time for comparison
        $now = new \DateTimeImmutable();

        // Call the buildForm method
        $urlBlockType->buildForm($formBuilder, []);

        // Assert that data was captured and it's a DateTimeImmutable
        $this->assertNotNull($capturedData);
        $this->assertInstanceOf(\DateTimeImmutable::class, $capturedData);

        // Assert that the captured time is close to now (within 10 seconds)
        $diff = $now->getTimestamp() - $capturedData->getTimestamp();
        $this->assertLessThanOrEqual(10, abs($diff),
            'The default DateTimeImmutable should be close to the current time');
    }
}