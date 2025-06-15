<?php

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
 *
 * Tests for the RegistrationType form class.
 */
class RegistrationTypeTest extends TestCase
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
        
        // Define expectations for the add method calls
        $formBuilder->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [
                    'email',
                    EmailType::class,
                    $this->callback(function ($options) {
                        return isset($options['label']) 
                            && $options['label'] === 'label.email'
                            && isset($options['required']) 
                            && $options['required'] === true
                            && isset($options['attr']['max_length']) 
                            && $options['attr']['max_length'] === 191
                            && isset($options['constraints']) 
                            && is_array($options['constraints'])
                            && count($options['constraints']) === 1
                            && $options['constraints'][0] instanceof NotBlank;
                    })
                ],
                [
                    'password',
                    RepeatedType::class,
                    $this->callback(function ($options) {
                        return isset($options['type']) 
                            && $options['type'] === PasswordType::class
                            && isset($options['required']) 
                            && $options['required'] === true
                            && isset($options['constraints']) 
                            && is_array($options['constraints'])
                            && count($options['constraints']) === 2
                            && $options['constraints'][0] instanceof Length
                            && $options['constraints'][1] instanceof NotBlank
                            && isset($options['first_options']) 
                            && $options['first_options']['label'] === 'label.password'
                            && isset($options['second_options']) 
                            && $options['second_options']['label'] === 'label.repeat_password';
                    })
                ]
            );
        
        // Create an instance of the RegistrationType class
        $registrationType = new RegistrationType();
        
        // Call the buildForm method
        $registrationType->buildForm($formBuilder, []);
    }

    /**
     * Test getBlockPrefix method.
     */
    public function testGetBlockPrefix(): void
    {
        // Create an instance of the RegistrationType class
        $registrationType = new RegistrationType();

        // Test that the getBlockPrefix method returns the expected value
        $this->assertEquals('user', $registrationType->getBlockPrefix());
    }

    /**
     * Test validation constraints on the form fields.
     */
    public function testFormConstraints(): void
    {
        // Create a mock for the FormBuilderInterface
        $formBuilder = $this->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Setup method to capture the constraints
        $capturedEmailConstraints = null;
        $capturedPasswordConstraints = null;

        // Define a custom callback for the first add call (email field)
        $formBuilder->method('add')
            ->willReturnCallback(function ($fieldName, $fieldType, $options) use ($formBuilder, &$capturedEmailConstraints, &$capturedPasswordConstraints) {
                if ($fieldName === 'email') {
                    $capturedEmailConstraints = $options['constraints'] ?? [];
                } elseif ($fieldName === 'password') {
                    $capturedPasswordConstraints = $options['constraints'] ?? [];
                }
                return $formBuilder;
            });

        // Create an instance of the RegistrationType class
        $registrationType = new RegistrationType();

        // Call the buildForm method
        $registrationType->buildForm($formBuilder, []);

        // Verify email constraints
        if ($capturedEmailConstraints !== null) {
            $this->assertCount(1, $capturedEmailConstraints);
            $this->assertInstanceOf(NotBlank::class, $capturedEmailConstraints[0]);
        }

        // Verify password constraints
        if ($capturedPasswordConstraints !== null) {
            $this->assertCount(2, $capturedPasswordConstraints);

            // Find the Length constraint
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

            // Verify NotBlank constraint exists
            $this->assertContainsOnlyInstancesOf(NotBlank::class,
                array_filter($capturedPasswordConstraints, function ($constraint) {
                    return $constraint instanceof NotBlank;
                })
            );
        }
    }
}