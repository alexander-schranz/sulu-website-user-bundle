<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractUserType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface  $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user_class' => User::class,
            'contact_type' => ContactType::class,
            'contact_type_options' => [],
            'contact_address_type' => ContactAddressType::class,
            'contact_address_type_options' => [],
            'address_type' => AddressType::class,
            'address_type_options' => [],
            'type' => null,
            'locale' => 'en',
            'locales' => [
                'en' => 'EN',
                'de' => 'DE',
            ],
        ]);
    }
}
