<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
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

        $user = $this->getNewUser();
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
     * @return mixed
     */
    protected function getNewUser()
    {
        return $this->getUserRepository()->createNew();
    }
}
