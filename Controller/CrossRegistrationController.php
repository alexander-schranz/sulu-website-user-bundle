<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\SecurityBundle\Entity\Role;
use Sulu\Bundle\SecurityBundle\Entity\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrossRegistrationController extends AbstractController
{
    /**
     * @param Request $request
     * @param string $webSpaceKey
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws AccessDeniedHttpException
     */
    public function crossRegistrationAction(Request $request, $webSpaceKey)
    {
        $this->checkSecuritySystem(Configuration::TYPE_CROSS_REGISTRATION);

        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException();
        }

        $userRole = new UserRole();
        $userRole->setRole($this->getWebSpaceRole($webSpaceKey));
        $userRole->setUser($user);
        $userRole->setLocale($this->getWebSpaceLocales($webSpaceKey));

        return $this->handleForm(
            $request,
            Configuration::TYPE_CROSS_REGISTRATION,
            $userRole
        );
    }

    /**
     * @param string $webSpaceKey
     *
     * @return Role
     */
    protected function getWebSpaceRole($webSpaceKey)
    {
        $roleName = $this->getConfig(null, Configuration::ROLE, $webSpaceKey);

        if (!$roleName) {
            throw new NotFoundHttpException(
                sprintf(
                    'Webspace "%s" not found',
                    $webSpaceKey
                )
            );
        }

        // Get or Create Role
        $roleRepository = $this->get('sulu.repository.role');

        $system = $this->getWebSpaceSystem($webSpaceKey);
        $role = $roleRepository->findOneBy(['name' => $roleName, 'system' => $system]);

        if (!$role) {
            /** @var Role $role */
            $role = $roleRepository->createNew();
            $role->setSystem($system);
            $role->setName($roleName);

            $entityManager = $this->get('doctrine.orm.default_entity_manager');
            $entityManager->persist($role);
            $entityManager->flush();
        }

        return $role;
    }
}
