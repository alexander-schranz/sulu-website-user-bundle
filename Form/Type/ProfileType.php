<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email');

        if (count($options['locales']) > 1) {
            $builder->add('locale', 'choice', [
                'choices' => $options['locales'],
                'data' => $options['locale'],
            ]);
        } else {
            $builder->add('locale', 'hidden', ['data' => $options['locale']]);
        }

        $builder->add('password', 'password', [
            'constraints' => new UserPassword([
                'groups' => 'profile_password',
            ]),
            'required' => false,
            'mapped' => false,
        ]);

        $builder->add('plainPassword', 'repeated', [
            'first_name' => 'new_password',
            'second_name' => 'confirm',
            'type' => 'password',
            'required' => false,
            'mapped' => false,
            'constraints' => new NotBlank([
                'groups' => 'profile_password',
            ]),
        ]);

        $builder->add('contact', new $options['contact_type'], $options['contact_type_options']);

        $builder->add('submit', 'submit');
    }

    public function getName()
    {
        return Configuration::TYPE_PROFILE;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('validation_groups', function (FormInterface $form) {
            if (!empty($form->get('password')->getData())) {
                return array('profile', 'profile_password');
            }

            return array('profile');
        });
    }
}