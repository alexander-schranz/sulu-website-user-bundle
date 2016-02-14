<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Sulu\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\Form;

interface HandlerInterface
{
    /**
     * @param Form $form
     * @param string $webSpaceKey
     * @param array $options
     *
     * @return mixed|User
     */
    public function handle(Form $form, $webSpaceKey, array $options = []);
}