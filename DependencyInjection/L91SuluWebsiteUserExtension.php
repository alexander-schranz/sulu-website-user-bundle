<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class L91SuluWebsiteUserExtension extends Extension
{
    /**
     * @var string
     */
    private $webSpaceKey;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config[Configuration::WEBSPACES] as $webSpaceKey => $webSpaceConfig) {
            $this->webSpaceKey = $webSpaceKey;
            // Set WebSpace Default Mail Config
            foreach (Configuration::$MAIL_CONFIGS as $mailConfig) {
                $this->setParameter($container, $mailConfig, $webSpaceConfig[$mailConfig]);
            }
            // Set WebSpace Role Name
            $this->setParameter($container, Configuration::ROLE, $webSpaceConfig[Configuration::ROLE]);
            // Set WebSpace Form Types
            foreach (Configuration::$FORM_TYPES as $formType) {
                $formTypes = $webSpaceConfig[Configuration::FORM_TYPES];
                $value = self::getValue($formType, $formTypes);
                $this->setParameter($container, $formType, $value, Configuration::FORM_TYPES);
            }
            // Set WebSpace Type Config
            foreach (Configuration::$TYPES as $type) {
                $typeConfig = $webSpaceConfig[$type];
                // Set WebSpace Type Mail Config
                foreach (Configuration::$MAIL_CONFIGS as $mailConfig) {
                    $value = self::getValue($mailConfig, $typeConfig, $webSpaceConfig);
                    $this->setParameter($container, $mailConfig, $value, $type);
                }
                // Set Activate Config
                if (isset($typeConfig[Configuration::ACTIVATE_USER])) {
                    $this->setParameter(
                        $container,
                        Configuration::ACTIVATE_USER,
                        $typeConfig[Configuration::ACTIVATE_USER],
                        $type
                    );
                }

                // Set Form Type
                if (isset($typeConfig[Configuration::FORM_TYPE])) {
                    $this->setParameter(
                        $container,
                        Configuration::FORM_TYPE,
                        $typeConfig[Configuration::FORM_TYPE],
                        $type
                    );
                }

                // Set WebSpace Type Templates
                foreach (Configuration::$TEMPLATES as $template) {
                    $value = self::getValue($template, $typeConfig[Configuration::TEMPLATES]);
                    $this->setParameter($container, Configuration::TEMPLATES . '.' . $template, $value, $type);
                }

                // Set WebSpace Login Embed Template
                if ($type === Configuration::TYPE_LOGIN) {
                    $template = Configuration::TEMPLATE_FORM_EMBED;
                    $value = self::getValue($template, $typeConfig[Configuration::TEMPLATES]);
                    $this->setParameter($container, Configuration::TEMPLATES . '.' . $template, $value, $type);
                }
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param $key
     * @param $config
     * @param array $fallbackConfig
     * @param null $default
     *
     * @return null
     */
    protected static function getValue($key, $config, $fallbackConfig = array(), $default = null)
    {
        if (empty($config[$key])) {
            if (!isset($fallbackConfig[$key])) {
                return $default;
            }

            return $fallbackConfig[$key];
        }

        return $config[$key];
    }

    /**
     * @param ContainerInterface $container
     * @param $key
     * @param $value
     * @param null $type
     */
    protected function setParameter(ContainerInterface $container, $key, $value, $type = null)
    {
        $container->setParameter(
            Configuration::ROOT . '.' . $this->webSpaceKey . ($type ? '.' . $type : '') . '.' . $key,
            $value
        );
    }
}
