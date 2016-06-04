<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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

class RegistrationHandler extends AbstractUserHandler
{
    /**
     * @var EntityRepository
     */
    protected $roleRepository;

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
        $this->roleRepository = $this->entityManager->getRepository(Role::class);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $webSpaceKey, array $options = [])
    {
        $user = $form->getData();

        $user = $this->setUserData($form, $user);

        if ($user instanceof BaseUser) {
            $user->setConfirmationKey($this->getUniqueToken());

            if ($options[Configuration::ACTIVATE_USER]) {
                $user->setEnabled(true);
            } else {
                $user->setEnabled(false);
            }
        }

        if ($user instanceof UserInterface) {
            $this->addUserRole($user, $options['system'], $options[Configuration::ROLE], $options['locales']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param $user
     * @param $system
     * @param $roleName
     * @param $locales
     *
     * @return mixed
     */
    private function addUserRole(UserInterface $user, $system, $roleName, $locales)
    {
        $role = $this->roleRepository->findOneBy([
            'system' => $system,
            'name' => $roleName,
        ]);

        // create role when not exists
        if (!$role) {
            /** @var Role $role */
            $role = $this->roleRepository->createNew();
            $role->setSystem($system);
            $role->setName($roleName);

            $this->entityManager->persist($role);
        }

        // create new user roles
        $userRole = new UserRole();
        $userRole->setRole($role);
        $userRole->setUser($user);
        $locales = json_encode(array_values($locales));
        $userRole->setLocale($locales);
        $this->entityManager->persist($userRole);
    }

    /**
     * @return string a unique token
     */
    protected function getUniqueToken()
    {
        $token = $this->tokenGenerator->generateToken();

        $user = $this->userRepository->findOneBy(['confirmationKey' => $token]);

        if (!$user) {
            return $token;
        }

        return $this->getUniqueToken();
    }
}
