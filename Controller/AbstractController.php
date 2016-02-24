<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use L91\Sulu\Bundle\WebsiteUserBundle\Form\HandlerInterface;
use L91\Sulu\Bundle\WebsiteUserBundle\Mail\MailHelperInterface;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\SecurityBundle\Entity\UserRepository;
use Sulu\Bundle\WebsiteBundle\Resolver\RequestAnalyzerResolverInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractController extends Controller
{
    /**
     * @param Request $request
     * @param string $type
     * @param mixed $data
     * @param array $options
     *
     * @return Response
     */
    protected function handleForm(Request $request, $type, $data = null, array $options = [])
    {
        $options = $this->getFormOptions($request, $type, $options);

        $form = $this->createForm($this->getFormType($type), $data, $options);
        $form->handleRequest($request);

        $valid = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getHandler($type)->handle(
                $form,
                $this->getWebSpaceKey(),
                $this->getHandlerOptions($request, $type)
            );

            if ($user) {
                $valid = true;
                $this->sendMails($type, $user);

                if ($this->doSuccessRedirect()) {
                    return $this->getValidRedirect($request);
                }
            }
        }

        return $this->render(
            $this->getTemplate($type, Configuration::TEMPLATE_FORM),
            [
                'form' => $form->createView(),
                'valid' => $valid,
            ]
        );
    }

    /**
     * @param $type
     *
     * @return HandlerInterface
     */
    protected function getHandler($type)
    {
        return $this->get(sprintf('%s.%s.handler', Configuration::ROOT, $type));
    }

    /**
     * @param Request $request
     * @param $type
     *
     * @return array
     */
    protected function getHandlerOptions(Request $request, $type)
    {
        return [
            'type' => $type,
            'system' => $this->getWebSpaceSystem(),
            'locales' => $this->getWebSpaceLocales(),
            'locale' => $request->getLocale(),
            Configuration::ROLE => $this->getConfig(null, Configuration::ROLE),
            Configuration::ACTIVATE_USER => $this->getConfig(
                $type === Configuration::TYPE_PASSWORD_RESET ? Configuration::TYPE_CONFIRMATION : $type,
                Configuration::ACTIVATE_USER
            ),
        ];
    }

    /**
     * @param Request $request
     * @param $type
     * @param $options
     *
     * @return array
     */
    protected function getFormOptions(Request $request, $type, $options) {
        $defaultOptions = [
            'locale' => $request->getLocale(),
            'locales' => $this->getWebSpaceLocales(),
            'type' => $type,
            'contact_type' => $this->getConfig(Configuration::FORM_TYPES, Configuration::FORM_TYPE_CONTACT),
            'contact_address_type' => $this->getConfig(Configuration::FORM_TYPES, Configuration::FORM_TYPE_CONTACT_ADDRESS),
            'address_type' => $this->getConfig(Configuration::FORM_TYPES, Configuration::FORM_TYPE_ADDRESS),
            'user_class' => $this->getUserClass(),
            'contact_type_options' => [
                'label' => false,
                'type' => $type,
                'locale' => $request->getLocale(),
            ],
            'contact_address_type_options' => [
                'label' => false,
                'type' => $type,
                'locale' => $request->getLocale(),
            ],
            'address_type_options' => [
                'label' => false,
                'type' => $type,
                'locale' => $request->getLocale(),
            ],
        ];

        if (in_array($type, [Configuration::TYPE_REGISTRATION, Configuration::TYPE_PROFILE])) {
            $defaultOptions['data_class'] = $this->getUserClass();
        }

        return array_merge($defaultOptions, $options);
    }

    /**
     * @param string $type
     * @param UserInterface $user
     */
    protected function sendMails(
        $type,
        UserInterface $user
    ) {
        if ($user instanceof BaseUser) {
            // get WebSpace type specific config
            $from = $this->getConfig($type, Configuration::MAIL_FROM);
            $to = $this->getConfig($type, Configuration::MAIL_TO);
            $subject = $this->getConfig($type, Configuration::MAIL_SUBJECT);
            $replyTo = $this->getConfig($type, Configuration::MAIL_REPLY_TO);

            $adminTemplate = $this->getTemplate($type, Configuration::TEMPLATE_ADMIN);
            $userTemplate = $this->getTemplate($type, Configuration::TEMPLATE_USER);

            if ($userTemplate) {
                // send email to user
                $body = $this->renderView($userTemplate, ['user' => $user]);

                $this->getMailHelper()->send(
                    $from,
                    $user->getEmail(),
                    $subject,
                    $body,
                    $replyTo
                );
            }

            if ($adminTemplate) {
                // send email to admin
                $body = $this->renderView($adminTemplate, ['user' => $user]);

                $this->getMailHelper()->send(
                    $from,
                    $to,
                    $subject,
                    $body,
                    $user->getEmail()
                );
            }
        }
    }

    /**
     * @param string $type
     *
     * @return bool
     *
     * @throws NotFoundHttpException
     */
    protected function checkSecuritySystem($type = null)
    {
        if ($webSpace = $this->getRequestAnalyser()->getWebspace()) {
            if ($security = $webSpace->getSecurity()) {
                if ($system = $security->getSystem()) {
                    if ($system) {
                        if ($type) {
                            if (!$this->getTemplate($type, Configuration::TEMPLATE_FORM)) {
                                throw new NotFoundHttpException();
                            }
                        }

                        return true;
                    }
                }
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    protected function getValidRedirect(Request $request)
    {
        return new RedirectResponse(
            $request->getPathInfo() . '?send=true'
        );
    }

    /**
     * @param $type
     * @param $key
     *
     * @return string
     */
    protected function getConfig($type, $key)
    {
        $parameter =
            Configuration::ROOT . '.' .
            $this->getWebSpaceKey() .
            ($type ? '.' . $type : '') . '.'
            . $key;

        if (!$this->container->hasParameter($parameter)) {
            return null;
        }

        return $this->container->getParameter($parameter);
    }

    /**
     * @param $type
     *
     * @return string
     */
    protected function getFormType($type)
    {
        $type = $this->getConfig($type, Configuration::FORM_TYPE);

        if (!$type) {
            throw new NotFoundHttpException('Form not found');
        }

        return new $type();
    }

    /**
     * @param string $type
     * @param string $template
     *
     * @return string
     */
    protected function getTemplate($type, $template)
    {
        return $this->getConfig($type, Configuration::TEMPLATES . '.' . $template);
    }

    /**
     * @return string|null
     */
    protected function getWebSpaceKey()
    {
        $webSpaceKey = null;

        if ($webSpace = $this->getRequestAnalyser()->getWebspace()) {
            $webSpaceKey = $webSpace->getKey();
        }

        return $webSpaceKey;
    }

    /**
     * @return string
     */
    protected function getWebSpaceSystem()
    {
        $system = null;

        if ($webSpace = $this->getRequestAnalyser()->getWebspace()) {
            $security = $webSpace->getSecurity();

            if ($security) {
                $system = $security->getSystem();
            }
        }

        return $system;
    }

    /**
     * @return array
     */
    protected function getWebSpaceLocales()
    {
        $webSpace = $this->getRequestAnalyser()->getWebspace();

        $locales = [];
        if ($webSpace) {
            foreach ($webSpace->getLocalizations() as $localization) {
                $locale = $localization->getLanguage();
                $locales[$locale] = $locale;
            }
        }

        return $locales;
    }

    /**
     * @return RequestAnalyzerInterface
     */
    protected function getRequestAnalyser()
    {
        $requestAnalyzer = $this->get('sulu_core.webspace.request_analyzer.website');

        $portal = $requestAnalyzer->getPortal();

        // SULU BUG FIXME https://github.com/sulu-io/sulu/issues/2041
        if (!$portal) {
            $request = $this->get('request_stack')->getMasterRequest();
            $requestAnalyzer->analyze($request);
        }

        return $requestAnalyzer;
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->get('sulu.repository.user');
    }

    /**
     * @return RequestAnalyzerResolverInterface
     */
    protected function getRequestAnalyserResolver()
    {
        return $this->get('sulu_website.resolver.request_analyzer');
    }

    /**
     * @return MailHelperInterface
     */
    protected function getMailHelper()
    {
        return$this->get('l91_sulu_website_user.mail_helper');
    }

    /**
     * {@inheritdoc}
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return parent::render(
            $view,
            $this->getTemplateAttributes($parameters),
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderView($view, array $parameters = array())
    {
        return parent::renderView($view, $this->getTemplateAttributes($parameters));
    }

    /**
     * @param array $custom
     * @return array
     */
    private function getTemplateAttributes($custom = array())
    {
        $defaults = [
            'isSecurityTemplate' => true,
            'extension' => [
                'excerpt' => [

                ],
                'seo' => [

                ],
            ],
            'content' => [],
            'shadowBaseLocale' => null
        ];

        $requestAnalyzer = $this->getRequestAnalyser();

        $default = array_merge(
            $defaults,
            $this->getRequestAnalyserResolver()->resolve($requestAnalyzer)
        );

        if (!isset($custom['urls'])) {
            $router = $this->get('router');
            $request = $this->get('request_stack')->getCurrentRequest();
            $urls = [];
            if ($request->get('_route')) {
                foreach ($requestAnalyzer->getWebspace()->getLocalizations() as $localization) {
                    $url = $router->generate(
                        $request->get('_route'),
                        $request->get('_route_params')
                    );

                    // will remove locale because it will be added automatically
                    if (preg_match('/^\/[a-z]{2}(-[a-z]{2})?+\/(.*)/', $url)) {
                        $url = substr($url, strlen($localization->getLocalization()) + 1);
                    }

                    $urls[$localization->getLocalization()] = $url;
                }
            }

            $custom['urls'] = $urls;
        }

        return array_merge(
            $default,
            $custom
        );
    }

    /**
     * @return bool
     */
    protected function doSuccessRedirect()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getUserClass()
    {
        return $this->getUserRepository()->getClassName();
    }
}
