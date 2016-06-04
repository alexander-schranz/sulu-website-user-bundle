<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Component\Security\Authentication\SaltGenerator;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class PasswordResetHandler extends AbstractUserHandler
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * PasswordResetHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param EncoderFactoryInterface $securityEncoderFactory
     * @param SaltGenerator $saltGenerator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EncoderFactoryInterface $securityEncoderFactory,
        SaltGenerator $saltGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->saltGenerator = $saltGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $webSpaceKey, array $options = [])
    {
        $data = $form->getData();

        try {
            $user = $this->userRepository->findUserByToken($data['token']);
        } catch (NoResultException $e) {
            return;
        }

        if ($user instanceof BaseUser) {
            $this->setPasswordAndSalt($form, $user);
            $user->setPasswordResetTokenExpiresAt(null);
            $user->setPasswordResetToken(null);

            if ($options[Configuration::ACTIVATE_USER]) {
                $user->setEnabled(true);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
