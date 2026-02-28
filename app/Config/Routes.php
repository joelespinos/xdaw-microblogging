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
    });

});
