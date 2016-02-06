<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;

class PasswordForgetController extends AbstractController
{
    public function forgetAction(Request $request)
    {
        $this->checkSecuritySystem(Configuration::TYPE_PASSWORD_FORGET);

        return $this->handleForm(
            $request,
            Configuration::TYPE_PASSWORD_FORGET
        );
    }
}
