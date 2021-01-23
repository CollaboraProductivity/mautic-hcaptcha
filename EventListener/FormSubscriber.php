<?php

/*
 * @copyright   2018 Konstantin Scheumann. All rights reserved
 * @author      Konstantin Scheumann
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticHcaptchaBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\Event\ValidationEvent;
use Mautic\FormBundle\FormEvents;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticHcaptchaBundle\Form\Type\HcaptchaType;
use MauticPlugin\MauticHcaptchaBundle\Integration\HcaptchaIntegration;
use MauticPlugin\MauticHcaptchaBundle\HcaptchaEvents;
use MauticPlugin\MauticHcaptchaBundle\Service\HcaptchaClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FormSubscriber implements EventSubscriberInterface
{
    const MODEL_NAME_KEY_LEAD = 'lead.lead';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var HcaptchaClient
     */
    protected $hcaptchaClient;

    /**
     * @var string
     */
    protected $siteKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var boolean
     */
    private $hcaptchaIsConfigured = false;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param IntegrationHelper        $integrationHelper
     * @param HcaptchaClient           $hcaptchaClient
     * @param LeadModel                $leadModel
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IntegrationHelper $integrationHelper,
        HcaptchaClient $hcaptchaClient,
        LeadModel $leadModel,
        TranslatorInterface $translator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->hcaptchaClient  = $hcaptchaClient;
        $integrationObject     = $integrationHelper->getIntegrationObject(HcaptchaIntegration::INTEGRATION_NAME);
        
        if ($integrationObject instanceof AbstractIntegration) {
            $keys            = $integrationObject->getKeys();
            $this->siteKey   = isset($keys['site_key']) ? $keys['site_key'] : null;
            $this->secretKey = isset($keys['secret_key']) ? $keys['secret_key'] : null;

            if ($this->siteKey && $this->secretKey) {
                $this->hcaptchaIsConfigured = true;
            }
        }
        $this->leadModel = $leadModel;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD         => ['onFormBuild', 0],
            HcaptchaEvents::ON_FORM_VALIDATE => ['onFormValidate', 0],
        ];
    }

    /**
     * @param FormBuilderEvent $event
     *
     * @throws \Mautic\CoreBundle\Exception\BadConfigurationException
     */
    public function onFormBuild(FormBuilderEvent $event)
    {
        if (!$this->hcaptchaIsConfigured) {
            return;
        }

        $event->addFormField('plugin.hcaptcha', [
            'label'          => 'mautic.plugin.actions.hcaptcha',
            'formType'       => HcaptchaType::class,
            'template'       => 'MauticHcaptchaBundle:Integration:hcaptcha.html.php',
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => false,
                'addDefaultValue'  => false,
                'addSaveResult'    => true,
            ],
            'site_key' => $this->siteKey,
        ]);

        $event->addValidator('plugin.hcaptcha.validator', [
            'eventName' => HcaptchaEvents::ON_FORM_VALIDATE,
            'fieldType' => 'plugin.hcaptcha',
        ]);
    }

    /**
     * @param ValidationEvent $event
     */
    public function onFormValidate(ValidationEvent $event)
    {
        if (!$this->hcaptchaIsConfigured) {
            return;
        }

        if ($this->hcaptchaClient->verify($event->getValue())) {
            return;
        }

        $event->failedValidation($this->translator === null ? 'hCaptcha was not successful.' : $this->translator->trans('mautic.integration.hcaptcha.failure_message'));

        $this->eventDispatcher->addListener(LeadEvents::LEAD_POST_SAVE, function (LeadEvent $event) {
            if ($event->isNew()){
                $this->leadModel->deleteEntity($event->getLead());
            }
        }, -255);
    }
}

