<?php

/*
 * @copyright   2018 Konstantin Scheumann. All rights reserved
 * @author      Konstantin Scheumann
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticHcaptchaBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class HcaptchaIntegration.
 */
class HcaptchaIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'Hcaptcha';

    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName()
    {
        return 'hCaptcha';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getRequiredKeyFields()
    {
        return [
            'site_key'   => 'mautic.integration.hcaptcha.site_key',
            'secret_key' => 'mautic.integration.hcaptcha.secret_key',
        ];
    }
}
