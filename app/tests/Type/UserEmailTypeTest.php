<?php

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\UserEmailType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEmailTypeTest extends TestCase
{
    private UserEmailType $userEmailType;

    protected function setUp(): void
    {
        $this->userEmailType = new UserEmailType();
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => User::class]);

        $this->userEmailType->configureOptions($resolver);
    }

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