<?php

namespace TM\UserBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraint;
use TM\UserBundle\Model\UserInterface;

abstract class CurrentUserType extends UserType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('current-password', PasswordType::class, [
                'mapped'        => false,
                'constraints'   => [
                    new UserPassword([
                        'groups'    => ['change_password'],
                    ]),
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'        => UserInterface::class,
                'validation_groups' => function (FormInterface $form) {
                    /** @var UserInterface $user */
                    $user = $form->getData();
                    $validationGroups = [Constraint::DEFAULT_GROUP];

                    if (null !== $user->getPlainPassword()) {
                        $validationGroups[] = 'change_password';
                    }

                    return $validationGroups;
                }
            ])
        ;
    }
}