<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Symfony\Component\Form\Form;

class ConfirmationHandler implements HandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $webSpaceKey, array $options = [])
    {
        $user = $form->getData();

        if ($user instanceof BaseUser) {
            $user->setConfirmationKey(null);

            if ($options[Configuration::ACTIVATE_USER]) {
                $user->setEnabled(true);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}