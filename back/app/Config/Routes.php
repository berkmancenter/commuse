<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('api/news', 'News::index');

$routes->get('api/people', 'People::index');
$routes->get('api/people/interests', 'People::interests');
$routes->get('api/people/(:num)', 'People::person/$1');

$routes->get('api/users/current', 'Users::current');
$routes->get('api/users/currentProfile', 'Users::currentProfile');
$routes->get('api/users/profileStructure', 'Users::profileStructure');
$routes->post('api/users/saveProfile', 'Users::saveProfile');
$routes->post('api/users/uploadProfileImage', 'Users::uploadProfileImage');

$routes->get('api/files/get/(.+)', 'Files::get/$1');

$routes->get('api/admin/invitations', 'Invitations::index');
$routes->post('api/admin/invitations/upsert', 'Invitations::upsert');
$routes->post('api/admin/invitations/delete', 'Invitations::delete');
$routes->get('api/admin/users', 'Users::adminIndex');
$routes->post('api/admin/users/delete', 'Users::delete');
$routes->post('api/admin/users/changeRole', 'Users::changeRole');
$routes->post('api/admin/users/importFromCsv', 'Users::importFromCsv');
$routes->get('api/admin/customFields', 'CustomFields::index');
$routes->post('api/admin/customFields/upsert', 'CustomFields::upsert');

$frontRoutes = [
  '/',
  'people',
  'profile',
  'account',
  'people/(:num)',
  'admin/users',
  'admin/invitations',
  'admin/custom_fields',
];

foreach ($frontRoutes as $route) {
  $routes->get($route, 'Front::index');
}

$routes->get('register', 'RegisterController::registerView');
$routes->post('register', 'RegisterController::registerAction');
$routes->get('changePassword', 'Users::changePasswordView');
$routes->post('changePassword', 'Users::changePassword');

service('auth')->routes($routes);

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
