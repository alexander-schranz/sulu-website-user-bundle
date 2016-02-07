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

        /*
        $builder->add(
            'contactAddresses',
            new ContactAddressType(),
            [
                'by_reference' => true,
            ]
        );
        */

        $builder->add(
            'contactAddresses',
            'collection',
            [
                'label' => false,
                'type' => new ContactAddressType(),
                'options' => [
                    'label' => false,
                ],
            ]
        );
    }

    public function getName()
    {
        return Configuration::TYPE_REGISTRATION;
    }
}