<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractContactAddressType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface  $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContactAddress::class,
            'address_type' => AddressType::class,
            'address_type_options' => ['label' => false],
            'type' => null,
            'locale' => null,
        ));
    }
}