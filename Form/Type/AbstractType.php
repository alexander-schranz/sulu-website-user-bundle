<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractType extends SymfonyAbstractType
{
    public function setDefaultOptions(OptionsResolverInterface  $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'locale' => 'en',
            'locales' => [
                'en' => 'EN',
                'de' => 'DE',
            ]
        ));
    }
}