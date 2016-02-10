<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registrationAction(Request $request)
    {
        $this->checkSecuritySystem();

        $user = new User();
        $contact = new Contact();
        $contactAddress = new ContactAddress();
        $contactAddress->setContact($contact);
        $contact->addContactAddress($contactAddress);
        $user->setContact($contact);

        return $this->handleForm(
            $request,
            Configuration::TYPE_REGISTRATION,
            $user
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function postFormHandle(UserInterface $user)
    {
        $entityManager = $this->getEntityManager();
        $roleRepository = $entityManager->getRepository(Role::class);

        $system = $this->getWebSpaceSystem();

        // only create role when system found
        if ($system) {
            // find role
            $roleName = $this->getRoleName();
            $role = $roleRepository->findBy([
                'system' => $system,
                'name' => $roleName
            ]);

            // create role when not exists
            if (!$role) {
                /** @var Role $role */
                $role = $roleRepository->createNew();
                $role->setSystem($system);
                $role->setName($roleName);

                $entityManager->persist($role);
            }

            // create new user roles
            $userRole = new UserRole();
            $userRole->setRole($role);
            $userRole->setUser($user);
            $locales = json_encode(array_values($this->getWebSpaceLocales()));
            $userRole->setLocale($locales);
            $entityManager->persist($userRole);

            // save user, role and user role
            $entityManager->flush();
        }

        return $user;
    }
}
