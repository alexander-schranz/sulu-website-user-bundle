<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\ContactAddress;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Security\Authentication\SaltGenerator;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

abstract class AbstractUserHandler implements HandlerInterface
{
    /**
     * @var SaltGenerator
     */
    protected $saltGenerator;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EncoderFactoryInterface
     */
    protected $securityEncoderFactory;

    /**
     * @param Form $form
     * @param $user
     *
     * @return $user
     */
    protected function setUserData(Form $form, $user)
    {
        if ($user instanceof BaseUser) {
            // set locale when not exist
            if (!$user->getLocale()) {
                $user->setLocale('en');
            }

            // set password when new or changed
            $this->setPasswordAndSalt($form, $user);
        }

        if ($user instanceof User) {
            // save contact and address when exists
            $this->persistContact($user);
        }

        return $user;
    }

    /**
     * @param User $user
     */
    protected function persistContact(User $user)
    {
        if ($contact = $user->getContact()) {
            $this->archiveEmail($user, $contact);
            $this->persistContactAddress($contact);
            $this->entityManager->persist($contact);
        }
    }

    /**
     * @param User $user
     * @param ContactInterface $contact
     */
    protected function archiveEmail(User $user, ContactInterface $contact)
    {
        if ($contact->getMainEmail() !== $user->getEmail()) {
            if ($contact->getMainEmail()) {
                $hasEmail = false;
                /** @var Email $email */
                foreach ($contact->getEmails() as $email) {
                    if ($email->getEmail() === $contact->getMainEmail()) {
                        $hasEmail = true;
                    }
                }

                if (!$hasEmail) {
                    $email = new Email();
                    $email->setEmail($contact->getMainEmail());
                    /** @var EmailType $emailType */
                    $emailType = $this->entityManager->getRepository(EmailType::class)->find(1);

                    if ($emailType) {
                        $email->setEmailType($emailType);
                        $contact->addEmail($email);
                    }
                }
            }

            $contact->setMainEmail($user->getEmail());
        }
    }

    /**
     * @param ContactInterface $contact
     */
    protected function persistContactAddress(ContactInterface $contact)
    {
        /** @var ContactAddress $contactAddress */
        foreach ($contact->getContactAddresses() as $contactAddress) {
            $this->entityManager->persist($contactAddress);
            if ($address = $contactAddress->getAddress()) {
                if (!$address->getAddressType()) {
                    $addressType = $this->entityManager->getRepository(AddressType::class)->find(1);
                    $address->setAddressType($addressType);
                }

                $this->entityManager->persist($address);
            }
        }
    }

    /**
     * @param Form $form
     * @param BaseUser $user
     */
    protected function setPasswordAndSalt(Form $form, BaseUser &$user)
    {
        if ($form->has('plainPassword') && $newPassword = $form->get('plainPassword')->getData()) {
            // generate salt if not exist
            if (!$user->getSalt()) {
                $user->setSalt($this->getRandomSalt());
            }

            $user->setPassword($this->getEncodedPassword($user, $newPassword));
        }
    }

    /**
     * @return string
     */
    protected function getRandomSalt()
    {
        return $this->saltGenerator->getRandomSalt();
    }

    /**
     * @param BaseUser $user
     * @param $newPassword
     *
     * @return string
     */
    protected function getEncodedPassword(BaseUser $user, $newPassword)
    {
        $encoder = $this->securityEncoderFactory->getEncoder($user);

        return $encoder->encodePassword($newPassword, $user->getSalt());
    }
}
