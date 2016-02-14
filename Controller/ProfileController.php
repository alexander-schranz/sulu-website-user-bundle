<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProfileController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileAction(Request $request)
    {
        $this->checkSecuritySystem(Configuration::TYPE_PROFILE);

        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException();
        }

        return $this->handleForm(
            $request,
            Configuration::TYPE_PROFILE,
            $user
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doSuccessRedirect()
    {
        return false;
    }
}
