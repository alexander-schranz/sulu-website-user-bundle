<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Symfony\Component\Form\FormBuilderInterface;

class AddressType extends AbstractAddressType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('primaryAddress', 'hidden', ['data' => 1]);
        $builder->add('street', 'text', ['required' => false]);
        $builder->add('number', 'text', ['required' => false]);
        $builder->add('addition', 'text', ['required' => false]);
        $builder->add('zip', 'text', ['required' => false]);
        $builder->add('city', 'text', ['required' => false]);
        $builder->add('state', 'text', ['required' => false]);
        $builder->add(
            'country',
            'entity',
            [
                'class' => Country::class,
                'property' => 'name',
            ]
        );
    }

    public function getName()
    {
        return Configuration::FORM_TYPE_ADDRESS;
    }
}