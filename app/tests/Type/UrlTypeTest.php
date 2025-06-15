<?php

namespace App\Tests\Form\Type;

use App\Entity\Url;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Form\Type\UrlType;
use App\Service\GuestUserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UrlTypeTest extends TestCase
{
    private TagsDataTransformer $tagsDataTransformer;
    private Security $security;
    private GuestUserService $guestUserService;
    private TranslatorInterface $translator;
    private UrlType $urlType;

    protected function setUp(): void
    {
        $this->tagsDataTransformer = $this->createMock(TagsDataTransformer::class);
        $this->security = $this->createMock(Security::class);
        $this->guestUserService = $this->createMock(GuestUserService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->urlType = new UrlType(
            $this->tagsDataTransformer,
            $this->security,
            $this->guestUserService,
            $this->translator
        );
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => Url::class,
            ]);

        $this->urlType->configureOptions($resolver);
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertEquals('Url', $this->urlType->getBlockPrefix());
    }

    public function testBuildFormForAuthenticatedUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $formField = $this->createMock(FormBuilderInterface::class);
        $formField->expects($this->once())
            ->method('addModelTransformer')
            ->with($this->tagsDataTransformer);

        $builder = $this->createMock(FormBuilderInterface::class);

        // Use exactly twice for the add method with consecutive return values
        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnSelf();

        $builder->method('get')
            ->with('tags')
            ->willReturn($formField);

        // Email field should not be added for authenticated users
        $builder->expects($this->never())
            ->method('addEventListener');

        $this->urlType->buildForm($builder, []);
    }

    public function testBuildFormForGuestUser(): void
    {
        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $formField = $this->createMock(FormBuilderInterface::class);

        $builder = $this->createMock(FormBuilderInterface::class);

        // We expect add to be called 3 times (longName, tags, email)
        $builder->expects($this->exactly(3))
            ->method('add')
            ->willReturnSelf();

        $builder->method('get')
            ->with('tags')
            ->willReturn($formField);

        // Test that form event listener is added
        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(FormEvents::SUBMIT, $this->callback(function($callback) {
                return is_callable($callback);
            }));

        $this->urlType->buildForm($builder, []);
    }

    public function testEmailLimitValidation(): void
    {
        // Create necessary mocks for this specific test
        $form = $this->createMock(FormInterface::class);
        $emailField = $this->createMock(FormInterface::class);
        $event = $this->createMock(FormEvent::class);

        // Setup email field to return test email
        $email = 'test@example.com';
        $emailField->expects($this->once())
            ->method('getData')
            ->willReturn($email);

        $form->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($emailField);

        // Fix: getForm() is called twice in the event listener
        $event->expects($this->exactly(2))
            ->method('getForm')
            ->willReturn($form);

        // Test when email has reached the limit
        $this->guestUserService->expects($this->once())
            ->method('countEmailUse')
            ->with($email)
            ->willReturn(10);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('message.daily_limit')
            ->willReturn('You have reached the daily limit.');

        $form->expects($this->once())
            ->method('addError')
            ->with($this->isInstanceOf(FormError::class));

        $builderMock = $this->createMock(FormBuilderInterface::class);
        $this->security->method('getUser')->willReturn(null);

        // Fix: Keep track of the builder in the callback scope
        $builderMock->method('addEventListener')->willReturnCallback(
            function ($eventName, $listener) use ($event, $builderMock) {
                if ($eventName === FormEvents::SUBMIT) {
                    $listener($event);
                }
                return $builderMock;
            }
        );

        // Execute buildForm to register the event listener
        $this->urlType->buildForm($builderMock, []);
    }
}