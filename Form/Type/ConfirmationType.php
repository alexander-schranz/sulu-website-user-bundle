<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form\Type;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
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
        $token = 'token';

        if (isset($options['data'])) {
            /** @var BaseUser $user */
            $user = $options['data'];
            $token = $user->getConfirmationKey();
        }

        $builder->setMethod('get');
        $builder->add('username', 'text');
        $builder->add('confirmation_key', 'text', [
            'error_bubbling' => false,
            'constraints' => [
                new EqualTo([
                    'groups' => $options['type'],
                    'value' => $token,
                    'message' => null, // Hide token output
                ]),
                new NotBlank([
                    'groups' => $options['type'],
                ]),
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
        $resolver->setDefault('csrf_protection', false);
    }
}