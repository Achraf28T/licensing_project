<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/admin/dashboard' => [[['_route' => 'admin_dashboard', '_controller' => 'App\\Controller\\AdminController::dashboard'], null, ['GET' => 0], null, false, false, null]],
        '/admin/licenses' => [[['_route' => 'admin_create_license', '_controller' => 'App\\Controller\\AdminController::createLicense'], null, ['POST' => 0], null, false, false, null]],
        '/admin/analytics/overview' => [[['_route' => 'admin_analytics_overview', '_controller' => 'App\\Controller\\AdminController::getAnalyticsOverview'], null, ['GET' => 0], null, false, false, null]],
        '/admin/analytics/licenses' => [[['_route' => 'admin_analytics_licenses', '_controller' => 'App\\Controller\\AdminController::getAnalyticsLicenses'], null, ['GET' => 0], null, false, false, null]],
        '/admin/analytics/activations' => [[['_route' => 'admin_analytics_activations', '_controller' => 'App\\Controller\\AdminController::getAnalyticsActivations'], null, ['GET' => 0], null, false, false, null]],
        '/admin/analytics/activities' => [[['_route' => 'admin_analytics_activities', '_controller' => 'App\\Controller\\AdminController::getAnalyticsActivities'], null, ['GET' => 0], null, false, false, null]],
        '/admin/simulate-test-data' => [[['_route' => 'admin_simulate_test_data', '_controller' => 'App\\Controller\\AdminController::simulateTestData'], null, ['POST' => 0], null, false, false, null]],
        '/demo' => [[['_route' => 'client_demo_home', '_controller' => 'App\\Controller\\ClientDemoController::index'], null, ['GET' => 0], null, false, false, null]],
        '/demo/api/fingerprint' => [[['_route' => 'client_demo_fingerprint', '_controller' => 'App\\Controller\\ClientDemoController::getFingerprint'], null, ['GET' => 0], null, false, false, null]],
        '/demo/api/activate' => [[['_route' => 'client_demo_activate', '_controller' => 'App\\Controller\\ClientDemoController::activate'], null, ['POST' => 0], null, false, false, null]],
        '/demo/api/validate' => [[['_route' => 'client_demo_validate', '_controller' => 'App\\Controller\\ClientDemoController::validate'], null, ['POST' => 0], null, false, false, null]],
        '/demo/api/report' => [[['_route' => 'client_demo_report', '_controller' => 'App\\Controller\\ClientDemoController::reportActivity'], null, ['POST' => 0], null, false, false, null]],
        '/sdk/activate' => [[['_route' => 'sdk_activate', '_controller' => 'App\\Controller\\SdkController::activate'], null, ['POST' => 0], null, false, false, null]],
        '/sdk/validate' => [[['_route' => 'sdk_validate', '_controller' => 'App\\Controller\\SdkController::validate'], null, ['POST' => 0], null, false, false, null]],
        '/sdk/report' => [[['_route' => 'sdk_report_activity', '_controller' => 'App\\Controller\\SdkController::reportActivity'], null, ['POST' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/admin/licenses/([^/]++)(*:31)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        31 => [
            [['_route' => 'admin_revoke_license', '_controller' => 'App\\Controller\\AdminController::revokeLicense'], ['id'], ['DELETE' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
