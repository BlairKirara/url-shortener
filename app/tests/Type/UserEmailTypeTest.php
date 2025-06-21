<?php

/**
 * Class UserEmailTypeTest.
 *
 * This class provides unit tests for UserEmailType.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\UserEmailType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserEmailTypeTest.
 */
class UserEmailTypeTest extends TestCase
{
    /**
     * User email type.
     */
    private UserEmailType $userEmailType;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        $this->userEmailType = new UserEmailType();
    }

    /**
     * Tests the configureOptions method.
     */
    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => User::class]);

        $this->userEmailType->configureOptions($resolver);
    }

    /**
     * Tests the buildForm method.
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once())
            ->method('add')
            ->with(
                'email',
                EmailType::class,
                [
                    'label' => 'label.email',
                    'required' => true,
                    'attr' => ['max_length' => 191],
                ]
            )
            ->willReturnSelf();

        $this->userEmailType->buildForm($builder, []);
    }
}
