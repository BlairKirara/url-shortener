<?php

/**
 * Class UserPasswordTypeTest.
 *
 * This class provides unit tests for UserPasswordType.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\UserPasswordType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserPasswordTypeTest.
 */
class UserPasswordTypeTest extends TestCase
{
    /**
     * User password type.
     *
     * @var UserPasswordType
     */
    private UserPasswordType $userPasswordType;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->userPasswordType = new UserPasswordType();
    }

    /**
     * Tests the configureOptions method.
     *
     * @return void
     */
    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => User::class]);

        $this->userPasswordType->configureOptions($resolver);
    }

    /**
     * Tests the buildForm method.
     *
     * @return void
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once())
            ->method('add')
            ->with(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'required' => true,
                    'first_options' => ['label' => 'label.password'],
                    'second_options' => ['label' => 'label.repeat_password'],
                ]
            )
            ->willReturnSelf();

        $this->userPasswordType->buildForm($builder, []);
    }
}