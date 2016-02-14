<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Sulu\Component\Security\Authentication\SaltGenerator;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ProfileHandler extends AbstractUserHandler
{
    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param SaltGenerator $saltGenerator
     * @param EncoderFactoryInterface $securityEncoderFactory
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(
        SaltGenerator $saltGenerator,
        EncoderFactoryInterface $securityEncoderFactory,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->saltGenerator = $saltGenerator;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $webSpaceKey, array $options = [])
    {
        $user = $form->getData();

        $user = $this->setUserData($form, $user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}