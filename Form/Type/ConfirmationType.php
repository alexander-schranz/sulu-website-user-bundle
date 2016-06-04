<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use L91\Sulu\Bundle\WebsiteUserBundle\Validator\Constraints\Exist;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfirmationType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('get');
        $builder->add('token', 'text', [
            'error_bubbling' => false,
            'constraints' => [
                new Exist([
                    'groups' => Configuration::TYPE_CONFIRMATION,
                    'columns' => ['confirmationKey'],
                    'entity' => $options['user_class'],
                ]),
                new NotBlank(),
            ],
        ]);

        $builder->add('submit', 'submit');
    }

    public function getName()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('validation_groups', Configuration::TYPE_CONFIRMATION);
        $resolver->setDefault('csrf_protection', false);
    }
}
