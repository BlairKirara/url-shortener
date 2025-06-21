<?php

/**
 * Class RegistrationTypeTest.
 *
 * This class provides unit tests for RegistrationType.
 */

namespace App\Tests\Type;

use App\Form\Type\RegistrationType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RegistrationTypeTest.
 */
class RegistrationTypeTest extends TestCase
{
    /**
     * Tests the buildForm method.
     */
    public function testBuildForm(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $formBuilder->method('add')->willReturn($formBuilder);

        $formBuilder->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [
                    'email',
                    EmailType::class,
                    $this->callback(function ($options) {
                        return isset($options['label'])
                            && 'label.email' === $options['label']
                            && isset($options['required'])
                            && true === $options['required']
                            && isset($options['attr']['max_length'])
                            && 191 === $options['attr']['max_length']
                            && isset($options['constraints'])
                            && is_array($options['constraints'])
                            && 1 === count($options['constraints'])
                            && $options['constraints'][0] instanceof NotBlank;
                    }),
                ],
                [
                    'password',
                    RepeatedType::class,
                    $this->callback(function ($options) {
                        return isset($options['type'])
                            && PasswordType::class === $options['type']
                            && isset($options['required'])
                            && true === $options['required']
                            && isset($options['constraints'])
                            && is_array($options['constraints'])
                            && 2 === count($options['constraints'])
                            && $options['constraints'][0] instanceof Length
                            && $options['constraints'][1] instanceof NotBlank
                            && isset($options['first_options'])
                            && 'label.password' === $options['first_options']['label']
                            && isset($options['second_options'])
                            && 'label.repeat_password' === $options['second_options']['label'];
                    }),
                ]
            );

        $registrationType = new RegistrationType();
        $registrationType->buildForm($formBuilder, []);
    }

    /**
     * Tests the getBlockPrefix method.
     */
    public function testGetBlockPrefix(): void
    {
        $registrationType = new RegistrationType();
        $this->assertEquals('user', $registrationType->getBlockPrefix());
    }

    /**
     * Tests validation constraints on the form fields.
     */
    public function testFormConstraints(): void
    {
        $formBuilder = $this->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $capturedEmailConstraints = null;
        $capturedPasswordConstraints = null;

        $formBuilder->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use ($formBuilder, &$capturedEmailConstraints, &$capturedPasswordConstraints) {
                if ('email' === $fieldName) {
                    $capturedEmailConstraints = $options['constraints'] ?? [];
                } elseif ('password' === $fieldName) {
                    $capturedPasswordConstraints = $options['constraints'] ?? [];
                }

                return $formBuilder;
            });

        $registrationType = new RegistrationType();
        $registrationType->buildForm($formBuilder, []);

        if (null !== $capturedEmailConstraints) {
            $this->assertCount(1, $capturedEmailConstraints);
            $this->assertInstanceOf(NotBlank::class, $capturedEmailConstraints[0]);
        }

        if (null !== $capturedPasswordConstraints) {
            $this->assertCount(2, $capturedPasswordConstraints);

            $lengthConstraint = null;
            foreach ($capturedPasswordConstraints as $constraint) {
                if ($constraint instanceof Length) {
                    $lengthConstraint = $constraint;
                    break;
                }
            }

            $this->assertNotNull($lengthConstraint, 'Length constraint not found');
            $this->assertEquals(6, $lengthConstraint->min);
            $this->assertEquals(191, $lengthConstraint->max);

            $this->assertContainsOnlyInstancesOf(
                NotBlank::class,
                array_filter($capturedPasswordConstraints, function ($constraint) {
                    return $constraint instanceof NotBlank;
                })
            );
        }
    }
}
