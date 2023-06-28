<?php

namespace App\Form\Type;

use App\Entity\Url;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Service\GuestUserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlType.
 */
class UrlType extends AbstractType
{
    /**
     * @var TagsDataTransformer
     */
    private TagsDataTransformer $tagsDataTransformer;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var GuestUserService
     */
    private GuestUserService $guestUserService;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param TagsDataTransformer $tagsDataTransformer
     * @param Security $security
     * @param GuestUserService $guestUserService
     * @param TranslatorInterface $translator
     */
    public function __construct(TagsDataTransformer $tagsDataTransformer, Security $security, GuestUserService $guestUserService, TranslatorInterface $translator)
    {
        $this->tagsDataTransformer = $tagsDataTransformer;
        $this->security = $security;
        $this->guestUserService = $guestUserService;
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'longName',
            TextType::class,
            [
                'label' => 'label.long_name',
                'required' => true,
                'attr' => ['max_length' => 255],
            ]
        );
        $builder->add(
            'tags',
            TextType::class,
            [
                'label' => 'label.tags',
                'required' => false,
                'attr' => ['max_length' => 70],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );

        if (!$this->security->getUser()) {
            $builder->add(
                'email',
                EmailType::class,
                [
                    'label' => 'label.email',
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['max_length' => 191],
                    'constraints' => [
                        new Email(),
                    ],
                ]
            );
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $email = $event->getForm()->get('email')->getData();
                $count = $this->guestUserService->countEmailUse($email);
                if ($count >= 10) {
                    $event->getForm()->addError(new FormError($this->translator->trans('message.daily_limit')));
                }
            });
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'Url';
    }
}
