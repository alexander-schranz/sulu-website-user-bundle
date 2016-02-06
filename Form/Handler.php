<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Component\Security\Authentication\SaltGenerator;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class Handler implements HandlerInterface
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
     * @var array
     */
    protected $handler = [];

    /**
     * @param SaltGenerator $saltGenerator
     * @param EncoderFactoryInterface $securityEncoderFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SaltGenerator $saltGenerator,
        EncoderFactoryInterface $securityEncoderFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->saltGenerator = $saltGenerator;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $type, $webSpaceKey)
    {
        if ($handler = $this->get($type, $webSpaceKey)) {
            // TODO allow specific handlers
            return $handler->handle($form, $type);
        }

        return $this->defaultHandler($form);
    }

    /**
     * {@inheritdoc}
     */
    public function add($type, $webSpaceKey, $handler)
    {
        if (!isset($this->handler[$type])) {
            $this->handler[$type] = [];
        }

        $this->handler[$type][$webSpaceKey] = $handler;
    }

    /**
     * @param $type
     * @param $webSpaceKey
     * @return mixed TODO
     */
    protected function get($type, $webSpaceKey)
    {
        if (isset($this->handler[$type]) && isset($this->handler[$type][$webSpaceKey]))
        {
            return $this->handler[$type][$webSpaceKey];
        }
    }

    /**
     * TODO own class DefaultHandler
     *
     * @param Form $form
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function defaultHandler(Form $form)
    {
        $entity = $form->getData();

        if ($entity instanceof User) {
            // set locale when not exist
            if (!$entity->getLocale()) {
                $entity->setLocale('en');
            }

            // set password
            if ($form->has('plainPassword') && $newPassword = $form->get('plainPassword')->getData()) {
                if ($newPassword) {
                    // generate salt if not exist
                    if (!$entity->getId() && !$entity->getSalt()) {
                        $entity->setSalt($this->saltGenerator->getRandomSalt());
                    }

                    $encoder = $this->securityEncoderFactory->getEncoder($entity);
                    $entity->setPassword($encoder->encodePassword($newPassword, $entity->getSalt()));
                }
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}