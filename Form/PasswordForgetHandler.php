<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\PasswordForgetType;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\SecurityBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\Form;

class PasswordForgetHandler implements HandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Form $form, $webSpaceKey, array $options = [])
    {
        $data = $form->getData();

        $usernameEmail = $data[PasswordForgetType::FIELD_NAME];

        $user = $this->userRepository->findUserByIdentifier($usernameEmail);

        if (!$user) {
            return $user;
        }

        if ($user instanceof BaseUser) {
            $user->setPasswordResetToken($this->getUniqueToken());
            $expireDateTime = (new \DateTime())->add(new \DateInterval('PT24H'));
            $user->setPasswordResetTokenExpiresAt($expireDateTime);
            $user->setPasswordResetTokenEmailsSent(
                $user->getPasswordResetTokenEmailsSent() + 1
            );
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return string a unique token
     */
    protected function getUniqueToken()
    {
        $token = $this->tokenGenerator->generateToken();

        try {
            $user = $this->userRepository->findUserByToken($token);

            if (!$user) {
                return $token;
            }
        } catch (NoResultException $ex) {
            return $token;
        }

        return $this->getUniqueToken();
    }
}
