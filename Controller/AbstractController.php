<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Controller;

use L91\Sulu\Bundle\WebsiteUserBundle\DependencyInjection\Configuration;
use L91\Sulu\Bundle\WebsiteUserBundle\Form\HandlerInterface;
use L91\Sulu\Bundle\WebsiteUserBundle\Form\Type\AbstractType;
use L91\Sulu\Bundle\WebsiteUserBundle\Loader\FormTypeLoaderInterface;
use L91\Sulu\Bundle\WebsiteUserBundle\Mail\MailHelperInterface;
use Sulu\Bundle\SecurityBundle\Entity\BaseUser;
use Sulu\Bundle\WebsiteBundle\Resolver\RequestAnalyzerResolverInterface;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractController extends Controller
{
    /**
     * @var RequestAnalyzerInterface
     */
    private $requestAnalyzer;

    /**
     * @var RequestAnalyzerResolverInterface
     */
    private $requestAnalyzerResolver;

    /**
     * @var FormTypeLoaderInterface
     */
    private $formTypeLoader;

    /**
     * @var HandlerInterface
     */
    protected $formHandler;

    /**
     * @var MailHelperInterface
     */
    protected $mailHelper;

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
        $defaultOptions = [
            'locale' => $request->getLocale(),
            'locales' => $this->getWebSpaceLocales(),
        ];

        $options = array_merge($defaultOptions, $options);

        $form = $this->createForm($this->getFormType($type), $data, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->handleFormSubmit($form, $type)) {
                return new RedirectResponse(
                    $request->getPathInfo() . '?send=true'
                );
            }
        }

        return $this->render(
            $this->getTemplate($type, Configuration::TEMPLATE_FORM),
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Form $form
     * @param string $type
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function handleFormSubmit(Form $form, $type)
    {
        $entity = $this->getFormHandler()->handle($form, $type, $this->getWebSpaceKey());

        return $entity;
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
     * @param string $type
     * @param BaseUser $user
     */
    protected function sendMails(
        $type,
        BaseUser $user
    ) {
        $from = $this->getConfig($type, Configuration::MAIL_FROM);
        $to = $this->getConfig($type, Configuration::MAIL_TO);
        $subject = $this->getConfig($type, Configuration::MAIL_SUBJECT);
        $replyTo = $this->getConfig($type, Configuration::MAIL_REPLY_TO);

        $adminTemplate = $this->getTemplate($type, Configuration::TEMPLATE_ADMIN);
        $userTemplate = $this->getTemplate($type, Configuration::TEMPLATE_USER);

        if ($userTemplate) {
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

    /**
     * @param $type
     * @param $key
     *
     * @return string
     */
    protected function getConfig($type, $key)
    {
        return $this->container->getParameter(
            Configuration::ROOT . '.' .
            $this->getWebSpaceKey() .
            ($type ? '.' . $type : '') . '.'
            . $key
        );
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
     * @return RequestAnalyzerInterface
     */
    protected function getRequestAnalyser()
    {
        if ($this->requestAnalyzer === null) {
            $this->requestAnalyzer = $this->get('sulu_core.webspace.request_analyzer');
        }

        return $this->requestAnalyzer;
    }

    /**
     * @return RequestAnalyzerResolverInterface
     */
    protected function getRequestAnalyserResolver()
    {
        if ($this->requestAnalyzerResolver === null) {
            $this->requestAnalyzerResolver = $this->get('sulu_website.resolver.request_analyzer');
        }

        return $this->requestAnalyzerResolver;
    }

    /**
     * @return HandlerInterface
     */
    protected function getFormHandler()
    {
        if ($this->formHandler === null) {
            $this->formHandler = $this->get('l91_sulu_website_user.form.handler');
        }

        return $this->formHandler;
    }

    /**
     * @return MailHelperInterface
     */
    protected function getMailHelper()
    {
        if ($this->mailHelper === null) {
            $this->mailHelper = $this->get('l91_sulu_website_user.mail_helper');
        }

        return $this->mailHelper;
    }

    /**
     * @param array $custom
     * @return array
     */
    private function getTemplateAttributes($custom = array())
    {
        $requestAnalyzer = $this->getRequestAnalyser();
        $default = array_merge(
            [
                'isSecurityTemplate' => true,
                'extension' => [
                    'excerpt' => [

                    ],
                    'seo' => [

                    ],
                ],
                'content' => [],
                'shadowBaseLocale' => null
            ],
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
}
