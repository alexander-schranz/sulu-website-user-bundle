<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use L91\Sulu\Bundle\WebsiteUserBundle\Validator\Constraints\Exist;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class ConfirmationType extends AbstractUserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('get');
        $builder->add('token', 'text', [
            'error_bubbling' => false,
            'constraints' => [
                new Exist([
                    'groups' => $options['type'],
                    'columns' => ['confirmationKey'],
                    'entity' => User::class,
                ]),
                new NotBlank(),
            ],
        ]);

        $builder->add('submit', 'submit');
    }

    public function getName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', null);
        $resolver->setDefault('csrf_protection', false);
    }
}