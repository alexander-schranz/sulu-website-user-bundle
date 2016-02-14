<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Symfony\Component\Form\Form;

class ConfirmationHandler implements HandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $webSpaceKey, array $options = [])
    {
        $data = $form->getData();

        $user = $this->userRepository->findOneBy(['confirmationKey' => $data['token']]);

        $user->setConfirmationKey(null);

        if ($options[Configuration::ACTIVATE_USER]) {
            if ($user instanceof BaseUser) {
                $user->setEnabled(true);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}