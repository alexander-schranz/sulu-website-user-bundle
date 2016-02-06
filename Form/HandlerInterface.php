<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\Form;

interface HandlerInterface
{
    /**
     * @param Form $form
     * @param string $type
     * @param string $webSpaceKey
     *
     * @return mixed|User
     *
     * @throws \Exception
     */
    public function handle(Form $form, $type, $webSpaceKey);

    /**
     * @param string $type
     * @param string $webSpaceKey
     * @param string $handler
     */
    public function add($type, $webSpaceKey, $handler);
}