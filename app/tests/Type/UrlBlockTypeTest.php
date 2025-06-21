<?php

/**
 * Class UrlBlockTypeTest.
 *
 * This class provides unit tests for UrlBlockType.
 */

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
 * Unit tests for the UrlBlockType form class.
 */
class UrlBlockTypeTest extends TestCase
{
    /**
     * Tests the buildForm method.
     */
    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder->method('add')->willReturn($formBuilder);

        $formBuilder->expects($this->once())
            ->method('add')
            ->with(
                'blockTime',
                DateTimeType::class,
                $this->callback(function ($options) {
                    $expectedYears = range(date('Y'), date('Y') + 5);

                    return isset($options['input']) && 'datetime_immutable' === $options['input']
                        && isset($options['label']) && 'label.block_time' === $options['label']
                        && isset($options['widget']) && 'choice' === $options['widget']
                        && isset($options['years']) && $options['years'] === $expectedYears
                        && isset($options['data']) && $options['data'] instanceof \DateTimeImmutable;
                })
            );

        $urlBlockType = new UrlBlockType();
        $urlBlockType->buildForm($formBuilder, []);
    }

    /**
     * Tests the configureOptions method.
     */
    public function testConfigureOptions(): void
    {
        $optionsResolver = $this->createMock(OptionsResolver::class);

        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => Url::class]);

        $urlBlockType = new UrlBlockType();
        $urlBlockType->configureOptions($optionsResolver);
    }

    /**
     * Tests the getBlockPrefix method.
     */
    public function testGetBlockPrefix(): void
    {
        $urlBlockType = new UrlBlockType();
        $this->assertEquals('BlockUrl', $urlBlockType->getBlockPrefix());
    }

    /**
     * Tests the default DateTimeImmutable value for blockTime.
     */
    public function testDefaultDateTimeImmutable(): void
    {
        $capturedData = null;
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $formBuilder->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use ($formBuilder, &$capturedData) {
                if ('blockTime' === $fieldName && isset($options['data'])) {
                    $capturedData = $options['data'];
                }

                return $formBuilder;
            });

        $urlBlockType = new UrlBlockType();
        $now = new \DateTimeImmutable();
        $urlBlockType->buildForm($formBuilder, []);

        $this->assertNotNull($capturedData);
        $this->assertInstanceOf(\DateTimeImmutable::class, $capturedData);
        $diff = $now->getTimestamp() - $capturedData->getTimestamp();
        $this->assertLessThanOrEqual(10, abs($diff), 'The default DateTimeImmutable should be close to the current time');
    }
}
