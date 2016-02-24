<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text');
        $builder->add('email', 'email');

        if (count($options['locales']) > 1) {
            $builder->add('locale', 'choice', [
                'choices' => $options['locales'],
                'data' => $options['locale'],
            ]);
        } else {
            $builder->add('locale', 'hidden', ['data' => $options['locale']]);
        }

        $builder->add('plainPassword', 'repeated', [
            'first_name'  => 'password',
            'second_name' => 'confirm',
            'type'        => 'password',
            'mapped'      => false,
        ]);

        $builder->add('contact', new $options['contact_type'], $options['contact_type_options']);

        $builder->add('submit', 'submit');
    }

    public function getName()
    {
        return Configuration::TYPE_REGISTRATION;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('validation_groups', Configuration::TYPE_REGISTRATION);
    }
}