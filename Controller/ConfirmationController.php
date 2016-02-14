<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConfirmationController extends AbstractController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction(Request $request)
    {
        $this->checkSecuritySystem(Configuration::TYPE_CONFIRMATION);

        $data = null;

        /** @var BaseUser $user */
        $user = $this->getUserRepository()->findOneBy(['username' => $request->get('username')]);

        if (!$user && $request->get('send') !== 'true') {
            throw new NotFoundHttpException();
        }

        if ($request->get('send') !== 'true' && !$user->getConfirmationKey()) {
            return $this->getValidRedirect($request);
        }

        return $this->handleForm(
            $request,
            Configuration::TYPE_CONFIRMATION,
            $user
        );
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository()
    {
        return $this->get('sulu_security.user_repository');
    }
}
