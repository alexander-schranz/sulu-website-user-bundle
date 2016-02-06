<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;

class ConfirmationController extends AbstractController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction(Request $request)
    {
        $this->checkSecuritySystem(Configuration::TYPE_CONFIRMATION);

        return $this->handleForm(
            $request,
            Configuration::TYPE_CONFIRMATION
        );
    }
}
