<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\Form\FormBuilderInterface;

class ContactAddressType extends AbstractContactAddressType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address', new $options['address_type'], $options['address_type_options']);

        $builder->add('main', 'hidden', [
            'required' => false,
            'data' => 1,
        ]);
    }

    public function getName()
    {
        return Configuration::TYPE_REGISTRATION;
    }
}