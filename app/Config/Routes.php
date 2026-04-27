<?php

// PLACEHOLDER UUID7
$routes->addPlaceholder('uuid7', '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}');

// PER DEFECTE
$routes->addRedirect('/', 'login');

// LOGIN
$routes->get('login', 'AuthFormsController::loginGet', ['as' => 'login', 'filter' => 'loggedIn']);
$routes->post('login', 'AuthFormsController::loginPost', ['filter' => 'loggedIn']);

// REGISTER
$routes->get('register', 'AuthFormsController::registerGet', ['filter' => 'loggedIn']);
$routes->post('register', 'AuthFormsController::registerPost', ['filter' => 'loggedIn']);

// LOGOUT
$routes->get('logout', 'AuthFormsController::logoutGet');

// CAPTCHA
$routes->get('captcha/refresh', 'CaptchaController::refresh');

// DASHBOARD
$routes->group('dashboard', ['filter' => 'authforms'], static function($routes) {

    $routes->get('', 'UserPagesController::dashboardGet', ['as' => 'dashboard']);

    // Obtenir imatges de writable
    $routes->get('media/(:uuid7)', 'ImagesMediaController::getImagesFromWritable/$1');

    $routes->group('piw', static function($routes) {

        $routes->addRedirect('/', 'dashboard');

        $routes->get('write', 'UserPagesController::writePiwladaGet');
        $routes->post('write', 'UserPagesController::writePiwladaPost');

        $routes->get('edit/(:uuid7)', 'UserPagesController::editPiwladaGet/$1', ['filter' => 'piwladaAuth']);
        $routes->post('edit/(:uuid7)', 'UserPagesController::editPiwladaPost/$1', ['filter' => 'piwladaAuth']);
        $routes->addRedirect('edit/', 'dashboard');
        
        $routes->post('delete/(:uuid7)', 'UserPagesController::deletePiwlada/$1', ['filter' => 'piwladaAuth']);
        $routes->addRedirect('delete/', 'dashboard');

        $routes->post('visibility/(:uuid7)', 'UserPagesController::visibilityPiwlada/$1', ['filter' => 'piwladaAuth']);
        $routes->addRedirect('visibility/', 'dashboard');

        $routes->get('comments/(:uuid7)', 'UserPagesController::commentsPiwladaGet/$1', ['filter' => 'commentsAcces']);
        $routes->post('comments/(:uuid7)', 'UserPagesController::commentsPiwladaPost/$1', ['filter' => 'commentsAcces']);
        $routes->addRedirect('comments/', 'dashboard');
    });
});

// Api-Rest Routes
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // Obtenir imatges de les piwlades
    $routes->get('media/(:uuid7)', 'MediaController::getMedia/$1');

    // Auth
    $routes->post('login',  'AuthController::login');
    $routes->post('logout', 'AuthController::logout', ['filter' => 'jwt:noRenew']);

    // Piwlades — públiques
    $routes->get('piwlada/(:uuid7)/comments', 'PiwladasController::getComments/$1',     ['filter' => 'publicPiwlada']);
    $routes->get('piwlada/(:uuid7)/author',   'PiwladasController::getBasicInfo/$1',    ['filter' => 'publicPiwlada']);
    $routes->get('piwlada/(:uuid7)/full',     'PiwladasController::getFullPiwlada/$1',  ['filter' => 'publicPiwlada']);
    $routes->get('piwlada/(:uuid7)',          'PiwladasController::getPiwlada/$1',      ['filter' => 'publicPiwlada']);

    // Usuaris — públics
    $routes->get('user/(:uuid7)', 'UserProfilesController::getUser/$1');

    // Piwlades — privades
    $routes->post('piwlada',                   'PiwladasController::createPiwlada',          ['filter' => 'jwt']);
    $routes->post('piwlada/(:uuid7)/comment',  'PiwladasController::commentOnPiwlada/$1',    ['filter' => ['jwt', 'publicPiwlada:withToken']]);
    $routes->post('piwlada/(:uuid7)/edit',     'PiwladasController::updatePiwlada/$1',       ['filter' => ['jwt', 'apiPiwladaAuth:timeExpiration']]);
    $routes->post('piwlada/(:uuid7)/status',   'PiwladasController::updatePiwladaStatus/$1', ['filter' => ['jwt', 'apiPiwladaAuth']]);
    $routes->delete('piwlada/(:uuid7)',        'PiwladasController::deletePiwlada/$1',       ['filter' => ['jwt', 'apiPiwladaAuth']]);

    // Usuaris — privats
    $routes->post('user/(:uuid7)/update', 'UserProfilesController::updateUser/$1', ['filter' => ['jwt', 'apiUserAuth']]);

    // Debug routes
    $routes->get('piwlada/all', 'PiwladasController::getAllPiwladas');
    $routes->get('user/all',    'UserProfilesController::getAllUsers',);
});
