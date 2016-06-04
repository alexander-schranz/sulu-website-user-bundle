<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use L91\Sulu\Bundle\WebsiteUserBundle\Validator\Constraints\Exist;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordResetType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('token', 'hidden', [
            'constraints' => [
                new Exist([
                    'columns' => ['passwordResetToken'],
                    'entity' => $options['user_class'],
                    'groups' => Configuration::TYPE_PASSWORD_RESET,
                    'message' => 'False token',
                ]),
                new NotBlank(),
            ],
        ]);

        $builder->add('plainPassword', 'repeated', [
            'first_name' => 'password',
            'second_name' => 'confirm',
            'type' => 'password',
            'mapped' => false,
        ]);

        $builder->add('submit', 'submit');
    }

    public function getName()
    {
        return Configuration::TYPE_PASSWORD_RESET;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('validation_groups', Configuration::TYPE_PASSWORD_RESET);
    }
}
