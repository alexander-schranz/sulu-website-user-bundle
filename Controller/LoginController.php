<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Component\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $this->checkSecuritySystem(Configuration::TYPE_LOGIN);

        return $this->render(
            $this->getTemplate(Configuration::TYPE_LOGIN, Configuration::TEMPLATE_FORM),
            $this->getLoginParams($request)
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getLoginParams(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            // last username entered by the user
            'last_username' => $lastUsername,
            'error' => $error,
        ];
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function embedAction(Request $request)
    {
        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->set(
            HttpCache::HEADER_REVERSE_PROXY_TTL,
            0
        );

        return $this->render(
            $this->getTemplate(Configuration::TYPE_LOGIN, Configuration::TEMPLATE_FORM_EMBED),
            [
                'user' => $this->getUser(),
            ],
            $response
        );
    }
}
