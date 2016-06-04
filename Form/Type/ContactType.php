<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\Form\FormBuilderInterface;

class ContactType extends AbstractContactType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'formOfAddress',
            'choice',
            [
                'choices' => [
                    'contact.contacts.formOfAddress.male',
                    'contact.contacts.formOfAddress.female',
                ],
                'translation_domain' => 'backend',
                'expanded' => true,
            ]
        );

        $builder->add('first_name', 'text');
        $builder->add('last_name', 'text');
        $builder->add('birthday', 'date', [
            'widget' => 'single_text',
            'required' => false,
        ]);

        if ($options['contact_address_type'] && $options['address_type']) {
            $builder->add(
                'contactAddresses',
                'collection',
                [
                    'label' => false,
                    'type' => new $options['contact_address_type'](),
                    'options' => $options['contact_address_type_options'],
                ]
            );
        }
    }

    public function getName()
    {
        return Configuration::FORM_TYPE_CONTACT;
    }
}
