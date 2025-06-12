<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/compte' => [[['_route' => 'app_account', '_controller' => 'App\\Controller\\AccountController::index'], null, null, null, false, false, null]],
        '/paiement/liste' => [[['_route' => 'app_abonnement_liste', '_controller' => 'App\\Controller\\PaiementController::listeAbonnements'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'app_home', '_controller' => 'App\\Controller\\HomeController::index'], null, null, null, false, false, null]],
        '/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, null, null, false, false, null]],
        '/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, null, null, false, false, null]],
        '/drops' => [[['_route' => 'app_drops', '_controller' => 'App\\Controller\\DropsController::index'], null, null, null, false, false, null]],
        '/ia-check' => [[['_route' => 'ia_check', '_controller' => 'App\\Controller\\IACheckController::index'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/facture/([^/]++)/telecharger(*:36)'
                .'|/paiement/abonnement/([^/]++)(*:72)'
                .'|/drops/(\\d+)(*:91)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        36 => [[['_route' => 'facture_telecharger', '_controller' => 'App\\Controller\\FactureController::telecharger'], ['id'], null, null, false, false, null]],
        72 => [[['_route' => 'app_paiement_abonnement', '_controller' => 'App\\Controller\\PaiementController::acheterAbonnement'], ['id'], ['POST' => 0], null, false, true, null]],
        91 => [
            [['_route' => 'app_drop_show', '_controller' => 'App\\Controller\\DropsController::show'], ['id'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
