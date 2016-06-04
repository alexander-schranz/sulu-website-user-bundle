<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection;

use L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\AddressType;
use L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ContactAddressType;
use L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ContactType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const ROOT = 'l91_sulu_website_user';
    const WEBSPACES = 'webspaces';

    const TYPE_LOGIN = 'login';
    const TYPE_REGISTRATION = 'registration';
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_PASSWORD_FORGET = 'password_forget';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_PROFILE = 'profile';

    const MAIL_FROM = 'from';
    const MAIL_TO = 'to';
    const MAIL_REPLY_TO = 'reply_to';
    const MAIL_SUBJECT = 'subject';

    const ROLE = 'role';

    const ACTIVATE_USER = 'activate_user';

    const FORM_TYPES = 'form_types';
    const FORM_TYPE_CONTACT = 'contact';
    const FORM_TYPE_CONTACT_ADDRESS = 'contact_address';
    const FORM_TYPE_ADDRESS = 'address';

    const FORM_TYPE = 'form_type';
    const TEMPLATES = 'templates';
    const TEMPLATE_FORM = 'form';
    const TEMPLATE_FORM_EMBED = 'form_embed';
    const TEMPLATE_ADMIN = 'admin';
    const TEMPLATE_USER = 'user';

    public static $FORM_TYPES = [
        self::FORM_TYPE_CONTACT,
        self::FORM_TYPE_CONTACT_ADDRESS,
        self::FORM_TYPE_ADDRESS,
    ];

    public static $TYPES = [
        self::TYPE_LOGIN,
        self::TYPE_REGISTRATION,
        self::TYPE_CONFIRMATION,
        self::TYPE_PASSWORD_FORGET,
        self::TYPE_PASSWORD_RESET,
        self::TYPE_PROFILE,
    ];

    public static $MAIL_CONFIGS = [
        self::MAIL_TO,
        self::MAIL_FROM,
        self::MAIL_SUBJECT,
        self::MAIL_REPLY_TO,
    ];

    public static $TEMPLATES = [
        self::TEMPLATE_FORM,
        self::TEMPLATE_ADMIN,
        self::TEMPLATE_USER,
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ROOT);

        $rootNode
            ->children()
                ->arrayNode(self::WEBSPACES)
                    ->prototype('array')
                        ->children()
                            // Basic Mail Config
                            ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                            ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                            ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                            ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                            ->scalarNode(self::ROLE)->defaultValue('Website')->end()
                            // Form Types
                            ->arrayNode(self::FORM_TYPES)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Contact, ContactAddress, Address Form Types
                                    ->scalarNode(self::FORM_TYPE_CONTACT)->defaultValue(ContactType::class)->end()
                                    ->scalarNode(self::FORM_TYPE_CONTACT_ADDRESS)->defaultValue(ContactAddressType::class)->end()
                                    ->scalarNode(self::FORM_TYPE_ADDRESS)->defaultValue(AddressType::class)->end()
                                ->end()
                            ->end()
                            // Login
                            ->arrayNode(self::TYPE_LOGIN)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Login Mail Config
                                    ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                                    // Login Templates
                                    ->arrayNode(self::TEMPLATES)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::TEMPLATE_FORM)->defaultValue('::templates/security/login.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_FORM_EMBED)->defaultValue('::templates/security/embed/login.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_ADMIN)->defaultValue(null)->end()
                                            ->scalarNode(self::TEMPLATE_USER)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Registration
                            ->arrayNode(self::TYPE_REGISTRATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Registration Mail Config
                                    ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                                    // Activate
                                    ->booleanNode(self::ACTIVATE_USER)->defaultValue(false)->end()
                                    // Registration Form
                                    ->scalarNode(self::FORM_TYPE)->defaultValue('L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\RegistrationType')->end()
                                    // Registration Templates
                                    ->arrayNode(self::TEMPLATES)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::TEMPLATE_FORM)->defaultValue('::templates/security/registration.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_ADMIN)->defaultValue(null)->end()
                                            ->scalarNode(self::TEMPLATE_USER)->defaultValue('::templates/security/emails/registration-user.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Confirmation
                            ->arrayNode(self::TYPE_CONFIRMATION)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Confirmation Mail Config
                                    ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                                    // Activate User
                                    ->booleanNode(self::ACTIVATE_USER)->defaultValue(true)->end()
                                    // Confirmation Form
                                    ->scalarNode(self::FORM_TYPE)->defaultValue('L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ConfirmationType')->end()
                                    // Confirmation Templates
                                    ->arrayNode(self::TEMPLATES)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::TEMPLATE_FORM)->defaultValue('::templates/security/confirmation.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_ADMIN)->defaultValue(null)->end()
                                            ->scalarNode(self::TEMPLATE_USER)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Password Forget
                            ->arrayNode(self::TYPE_PASSWORD_FORGET)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Password Forget Mail Config
                                    ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                                    // Password Forget Form
                                    ->scalarNode(self::FORM_TYPE)->defaultValue('L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\PasswordForgetType')->end()
                                    // Password Forget Templates
                                    ->arrayNode(self::TEMPLATES)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::TEMPLATE_FORM)->defaultValue('::templates/security/password-forget.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_ADMIN)->defaultValue(null)->end()
                                            ->scalarNode(self::TEMPLATE_USER)->defaultValue('::templates/security/emails/password-forget-user.html.twig')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Password Reset
                            ->arrayNode(self::TYPE_PASSWORD_RESET)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Password Reset Mail Config
                                    ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                                    // Password Reset Form
                                    ->scalarNode(self::FORM_TYPE)->defaultValue('L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\PasswordResetType')->end()
                                    // Password Reset Templates
                                    ->arrayNode(self::TEMPLATES)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::TEMPLATE_FORM)->defaultValue('::templates/security/password-reset.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_ADMIN)->defaultValue(null)->end()
                                            ->scalarNode(self::TEMPLATE_USER)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            // Profile
                            ->arrayNode(self::TYPE_PROFILE)
                                ->addDefaultsIfNotSet()
                                ->children()
                                    // Profile Mail Config
                                    ->scalarNode(self::MAIL_FROM)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_REPLY_TO)->defaultValue(null)->end()
                                    ->scalarNode(self::MAIL_SUBJECT)->defaultValue(null)->end()
                                    // Profile Form
                                    ->scalarNode(self::FORM_TYPE)->defaultValue('L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\ProfileType')->end()
                                    // Profile Templates
                                    ->arrayNode(self::TEMPLATES)
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode(self::TEMPLATE_FORM)->defaultValue('::templates/security/profile.html.twig')->end()
                                            ->scalarNode(self::TEMPLATE_ADMIN)->defaultValue(null)->end()
                                            ->scalarNode(self::TEMPLATE_USER)->defaultValue(null)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
