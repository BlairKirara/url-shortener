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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;


class UrlType extends AbstractType
{

    private TagsDataTransformer $tagsDataTransformer;


    private Security $security;

    private GuestUserService $guestUserService;

    private TranslatorInterface $translator;

    private RequestStack $requestStack;

    public function __construct(TagsDataTransformer $tagsDataTransformer, Security $security, GuestUserService $guestUserService, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->tagsDataTransformer = $tagsDataTransformer;
        $this->security = $security;
        $this->guestUserService = $guestUserService;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                        new NotBlank(),
                        new Length(['min' => 3, 'max' => 191]),
                        new Email(),
                    ],
                ]
            );
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $email = $event->getForm()->get('email')->getData();
                $request = $this->requestStack->getSession();
                $request->set('email', $email);

                $count = $this->guestUserService->countEmailsUsedInLast24Hours($email);
                if ($count >= 10) {
                    $event->getForm()->addError(new FormError($this->translator->trans('message.email_limit_exceeded')));
                }
            });
        }

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
                'attr' => ['max_length' => 64],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'Url';
    }
}
