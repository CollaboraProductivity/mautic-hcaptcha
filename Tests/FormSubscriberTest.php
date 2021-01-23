<?php

/*
 * @copyright   2018 Konstantin Scheumann. All rights reserved
 * @author      Konstantin Scheumann
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticHcaptchaBundle\Tests;

use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\FormBundle\Event\ValidationEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticHcaptchaBundle\EventListener\FormSubscriber;
use MauticPlugin\MauticHcaptchaBundle\Integration\HcaptchaIntegration;
use MauticPlugin\MauticHcaptchaBundle\Service\HcaptchaClient;
use PHPUnit_Framework_MockObject_MockBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mautic\FormBundle\Event\FormBuilderEvent;

class FormSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|HcaptchaIntegration
     */
    private $integration;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|ModelFactory
     */
    private $modelFactory;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|HcaptchaClient
     */
    private $hcaptchaClient;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|ValidationEvent
     */
    private $validationEvent;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder|FormBuilderEvent
     */
    private $formBuildEvent;

    protected function setUp()
    {
        parent::setUp();

        $this->integration       = $this->createMock(HcaptchaIntegration::class);
        $this->eventDispatcher   = $this->createMock(EventDispatcherInterface::class);
        $this->integrationHelper = $this->createMock(IntegrationHelper::class);
        $this->modelFactory      = $this->createMock(ModelFactory::class);
        $this->hcaptchaClient   = $this->createMock(HcaptchaClient::class);
        $this->validationEvent   = $this->createMock(ValidationEvent::class);
        $this->formBuildEvent    = $this->createMock(FormBuilderEvent::class);

        $this->eventDispatcher
            ->method('addListener')
            ->willReturn(true);

        $this->integration
            ->method('getKeys')
            ->willReturn(['site_key' => 'test', 'secret_key' => 'test']);
    }

    public function testOnFormValidateSuccessful()
    {
        $this->hcaptchaClient->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormValidateFailure()
    {
        $this->hcaptchaClient->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->validationEvent->expects($this->once())
            ->method('getValue')
            ->willReturn('any-value-should-work');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormValidateWhenPluginIsNotInstalled()
    {
        $this->hcaptchaClient->expects($this->never())
            ->method('verify');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(null);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormValidateWhenPluginIsNotConfigured()
    {
        $this->hcaptchaClient->expects($this->never())
            ->method('verify');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(['site_key' => '']);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormBuildWhenPluginIsInstalledAndConfigured()
    {
        $this->formBuildEvent->expects($this->once())
            ->method('addFormField')
            ->with('plugin.hcaptcha');

        $this->formBuildEvent->expects($this->once())
            ->method('addValidator')
            ->with('plugin.hcaptcha.validator');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->createFormSubscriber()->onFormBuild($this->formBuildEvent);
    }

    public function testOnFormBuildWhenPluginIsNotInstalled()
    {
        $this->formBuildEvent->expects($this->never())
            ->method('addFormField');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(null);

        $this->createFormSubscriber()->onFormBuild($this->formBuildEvent);
    }

    public function testOnFormBuildWhenPluginIsNotConfigured()
    {
        $this->formBuildEvent->expects($this->never())
            ->method('addFormField');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(['site_key' => '']);

        $this->createFormSubscriber()->onFormBuild($this->formBuildEvent);
    }

    /**
     * @return FormSubscriber
     */
    private function createFormSubscriber()
    {
        return new FormSubscriber(
            $this->eventDispatcher,
            $this->integrationHelper,
            $this->modelFactory,
            $this->hcaptchaClient
        );
    }
}
