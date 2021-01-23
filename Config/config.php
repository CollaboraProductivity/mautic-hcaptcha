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
    'author'      => 'Konstantin Scheumann and Collabora',

    'routes' => [

    ],

    'services' => [
        'events' => [
            'mautic.hcaptcha.event_listener.form_subscriber' => [
                'class'     => \MauticPlugin\MauticHcaptchaBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.integration',
                    'mautic.hcaptcha.service.hcaptcha_client',
                    'mautic.lead.model.lead',
                    'translator'
                ],
            ],
        ],
        'models' => [

        ],
        'others'=>[
            'mautic.hcaptcha.service.hcaptcha_client' => [
                'class'     => \MauticPlugin\MauticHcaptchaBundle\Service\HcaptchaClient::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.hcaptcha' => [
                'class'     => \MauticPlugin\MauticHcaptchaBundle\Integration\HcaptchaIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
    'parameters' => [

    ],
];

