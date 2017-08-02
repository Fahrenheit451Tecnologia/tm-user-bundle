<?php

namespace TM\UserBundle\Form\Type;

use Rollerworks\Bundle\PasswordStrengthBundle\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use TM\ResourceBundle\Exception\FormExtensionException;
use TM\ResourceBundle\Form\Type\LocaleType;
use TM\UserBundle\Model\User;
use TM\UserBundle\Model\UserInterface;
use TM\UserBundle\Model\ValueObject\AbstractUsernameInterface;
use TM\UserBundle\Model\ValueObject\FirstLastNameField;
use TM\UserBundle\Model\ValueObject\NameField;

abstract class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('email', RepeatedType::class, [
                'type'              => EmailType::class,
                'first_name'        => 'new',
                'second_name'       => 'confirmation',
                'invalid_message'   => 'emails do not match',
            ])
            ->add('password', RepeatedType::class, [
                'type'              => PasswordType::class,
                'first_name'        => 'new',
                'second_name'       => 'confirmation',
                'property_path'     => 'plainPassword',
                'invalid_message'   => 'passwords do not match',
                'json_api_pointer'  => 'relationships',
                'constraints'       => [
                    new NotBlank([
                        'groups'    => 'user_create',
                    ]),
                    new PasswordRequirements([
                        'minLength'         => 8,
                        'requireLetters'    => true,
                        'requireNumbers'    => true,
                        'requireCaseDiff'   => true,
                    ])
                ]
            ])
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('language', LocaleType::class, [
                'property_path'     => 'locale'
            ])
            ->add('name_type_name', TextType::class, [
                'property_path'     => 'nameTypeName',
                'required'  => false
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'addFields'))
            ->addEventListener(FormEvents::POST_SUBMIT, array($this, 'generateNameType'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'create'            => false,
                'data_class'        => \TM\AppBundle\Model\User::class,
            ])
            ->addAllowedTypes('create', ['boolean'])
            ->setNormalizer('validation_groups', function (Options $options, $validationGroups) {
                if (!is_array($validationGroups)) {
                    $validationGroups = [$validationGroups];
                }

                if (!in_array(Constraint::DEFAULT_GROUP, $validationGroups)) {
                    $validationGroups[] = Constraint::DEFAULT_GROUP;
                }

                if (!$options['create']) {
                    $value[] = 'user_create';
                }

                return $validationGroups;
            })
        ;
    }

    public function addFields(FormEvent $event)
    {
        /** @var FormInterface $form */
        $form = $event->getForm();
        /** @var array $submittedData */
        $submittedData = $event->getData();

        if (!is_array($submittedData)) {
            throw FormExtensionException::submittedDataMustBeAnArray($submittedData);
        }

        if (!array_key_exists('name_type', $submittedData)) {
            $submittedData['name_type'] = "first_last_name";
        }

        $options = ['mapped' => false];

        switch ($submittedData['name_type']) {
            case AbstractUsernameInterface::TYPE_FIRST_LAST_NAME:
                $form
                    ->add('firstName', TextType::class, array_merge($options))
                    ->add('lastName', TextType::class, array_merge($options))
                ;
                break;
            case AbstractUsernameInterface::TYPE_NAME:
                $form
                    ->add('Name', TextType::class, array_merge($options, [
                        'constraints'   => [
                            new NotBlank()
                        ]
                    ]));
                break;
            default:
                return;
        }
    }

    public function generateNameType(FormEvent $event)
    {
        /** @var FormInterface $form */
        $form = $event->getForm();

        /** @var UserInterface $user */
        $user = $form->getData();

        if(!$form->isValid()){
            return;
        }

        if(null === $type = $user->getNameTypeName()){
            $user->setNameTypeName("first_last_name");
            $type = "first_last_name";

            if(is_null($form->get('firstName')->getData()) && is_null($form->get('lastName')->getData())){
                return;
            }
        }

        switch ($type) {
            case AbstractUsernameInterface::TYPE_NAME:
                $usernameType = new NameField($form->get('Name')->getData());
                break;
            case AbstractUsernameInterface::TYPE_FIRST_LAST_NAME:
                $usernameType = new FirstLastNameField(
                    $form->get('firstName')->getData(),
                    $form->get('lastName')->getData()
                );
                break;
            default:
                throw new \Exception('type not recognised');
        }

        $user->setNameType($usernameType);
    }
}