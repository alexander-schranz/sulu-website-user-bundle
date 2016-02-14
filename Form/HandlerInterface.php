<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Form\Form;

interface HandlerInterface
{
    /**
     * @param Form $form
     * @param string $webSpaceKey
     * @param array $options
     *
     * @return UserInterface
     */
    public function handle(Form $form, $webSpaceKey, array $options = []);
}