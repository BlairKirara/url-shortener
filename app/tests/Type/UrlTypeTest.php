<?php

/**
 * Class UrlTypeTest.
 *
 * This class provides unit tests for UrlType.
 */

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

/**
 * Class UrlTypeTest.
 *
 * Unit tests for the UrlType form class.
 */
class UrlTypeTest extends TestCase
{
    /**
     * Tags data transformer.
     *
     * @var TagsDataTransformer
     */
    private TagsDataTransformer $tagsDataTransformer;

    /**
     * Security component.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Guest user service.
     *
     * @var GuestUserService
     */
    private GuestUserService $guestUserService;

    /**
     * Translator component.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * UrlType instance.
     *
     * @var UrlType
     */
    private UrlType $urlType;

    /**
     * Set up test environment.
     *
     * @return void
     */
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
            ->with([
                'data_class' => Url::class,
            ]);

        $this->urlType->configureOptions($resolver);
    }

    /**
     * Tests the getBlockPrefix method.
     *
     * @return void
     */
    public function testGetBlockPrefix(): void
    {
        $this->assertEquals('Url', $this->urlType->getBlockPrefix());
    }

    /**
     * Tests the buildForm method for an authenticated user.
     *
     * @return void
     */
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

        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnSelf();

        $builder->method('get')
            ->with('tags')
            ->willReturn($formField);

        $builder->expects($this->never())
            ->method('addEventListener');

        $this->urlType->buildForm($builder, []);
    }

    /**
     * Tests the buildForm method for a guest user.
     *
     * @return void
     */
    public function testBuildFormForGuestUser(): void
    {
        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $formField = $this->createMock(FormBuilderInterface::class);
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(3))
            ->method('add')
            ->willReturnSelf();

        $builder->method('get')
            ->with('tags')
            ->willReturn($formField);

        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(FormEvents::SUBMIT, $this->callback(function($callback) {
                return is_callable($callback);
            }));

        $this->urlType->buildForm($builder, []);
    }

    /**
     * Tests the email limit validation for guest users.
     *
     * @return void
     */
    public function testEmailLimitValidation(): void
    {
        $form = $this->createMock(FormInterface::class);
        $emailField = $this->createMock(FormInterface::class);
        $event = $this->createMock(FormEvent::class);

        $email = 'test@example.com';
        $emailField->expects($this->once())
            ->method('getData')
            ->willReturn($email);

        $form->expects($this->once())
            ->method('get')
            ->with('email')
            ->willReturn($emailField);

        $event->expects($this->exactly(2))
            ->method('getForm')
            ->willReturn($form);

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

        $builderMock->method('addEventListener')->willReturnCallback(
            function ($eventName, $listener) use ($event, $builderMock) {
                if ($eventName === FormEvents::SUBMIT) {
                    $listener($event);
                }
                return $builderMock;
            }
        );

        $this->urlType->buildForm($builderMock, []);
    }
}