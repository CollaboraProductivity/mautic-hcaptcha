<?php

/*
 * @copyright   2018 Konstantin Scheumann. All rights reserved
 * @author      Konstantin Scheumann
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'hCaptcha',
    'description' => 'Enables hCaptcha integration.',
    'version'     => '1.0',
    'author'      => 'Konstantin Scheumann',

    'routes' => [

    ],

    'services' => [
        'events' => [
            'mautic.hcaptcha.event_listener.form_subscriber' => [
                'class'     => \MauticPlugin\MauticHcaptchaBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.integration',
                    'mautic.model.factory',
                    'mautic.hcaptcha.service.hcaptcha_client'
                ],
            ],
            'mautic.hcaptcha.service.hcaptcha_client' => [
                'class'     => \MauticPlugin\MauticHcaptchaBundle\Service\HcaptchaClient::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.hcaptcha' => [
                'class' => \MauticPlugin\MauticHcaptchaBundle\Form\Type\HcaptchaType::class,
                'alias' => 'hcaptcha',
            ],
        ],
        'models' => [

        ],
        'integrations' => [
            'mautic.integration.hcaptcha' => [
                'class'     => \MauticPlugin\MauticHcaptchaBundle\Integration\HcaptchaIntegration::class,
                'arguments' => [
                ],
            ],
        ],
    ],
    'parameters' => [

    ],
];
