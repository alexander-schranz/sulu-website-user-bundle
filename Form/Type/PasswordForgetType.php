<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use L91\Sulu\Bundle\WebsiteUserBundle\Validator\Constraints\Exist;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordForgetType extends AbstractUserType
{
    /**
     * @var string
     */
    const FIELD_NAME = 'username_email';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::FIELD_NAME, 'text', [
            'constraints' => new Exist([
                'columns' => ['email', 'username'],
                'entity' => User::class,
                'groups' => [$options['type']],
            ]),
        ]);

        $builder->add('submit', 'submit');
    }

    public function getName()
    {
        return Configuration::TYPE_PASSWORD_FORGET;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', null);
    }
}