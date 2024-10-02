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
$routes->setDefaultController('Front');
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

$routes->post('api/people', 'People::index');
$routes->get('api/people/interests', 'People::interests');
$routes->get('api/people/(:num)', 'People::person/$1');
$routes->get('api/people/filters', 'People::filters');
$routes->get('api/people/export', 'People::export');
$routes->get('api/people/indexRemote', 'People::indexRemote');

$routes->get('api/users/current', 'Users::current');
$routes->get('api/users/profileStatus', 'Users::profileStatus');
$routes->get('api/users/profile/(:any)', 'Users::profile/$1');
$routes->get('api/users/profileStructure', 'Users::profileStructure');
$routes->post('api/users/saveProfile', 'Users::saveProfile');
$routes->post('api/users/savePublicProfileStatus', 'Users::savePublicProfileStatus');
$routes->post('api/users/uploadProfileImage/(:any)', 'Users::uploadProfileImage/$1');

$routes->get('api/files/get/(.+)', 'Files::get/$1');

$routes->get('api/admin/invitations', 'Invitations::index');
$routes->post('api/admin/invitations/upsert', 'Invitations::upsert');
$routes->post('api/admin/invitations/delete', 'Invitations::delete');
$routes->get('api/admin/users', 'Users::adminIndex');
$routes->post('api/admin/users/delete', 'Users::delete');
$routes->post('api/admin/users/changeRole', 'Users::changeRole');
$routes->post('api/admin/users/importFromCsv', 'Users::importFromCsv');
$routes->get('api/admin/users/csvImportTemplate', 'Users::getUsersCsvImportTemplate');
$routes->post('api/admin/users/setReintakeStatus', 'Users::setReintakeStatus');
$routes->get('api/admin/customFields', 'CustomFields::index');
$routes->post('api/admin/customFields/upsert', 'CustomFields::upsert');
$routes->post('api/admin/dataEditor', 'DataEditor::index');
$routes->post('api/admin/dataEditor/saveItem', 'DataEditor::saveItem');
$routes->get('api/admin/profileDataAudit', 'DataAudit::profileDataAudit');
$routes->post('api/admin/profileDataAudit/process', 'DataAudit::auditRecordProcess');
$routes->get('api/admin/profileDataAudit/getChangesFields', 'DataAudit::getChangesFields');
$routes->get('api/admin/systemSettings', 'SystemSettings::index');
$routes->post('api/admin/systemSettings', 'SystemSettings::saveSettings');
$routes->get('api/admin/publicSystemSettings', 'SystemSettings::getPublicSettings');
$routes->post('api/admin/users/setActiveAffiliation', 'Users::setActiveAffiliation');
$routes->post('api/admin/users/setActiveStatus', 'Users::setActiveStatus');
$routes->post('api/admin/users/createNewUser', 'Users::createNewUser');

// Buzz routes
$routes->get('api/buzz', 'BuzzController::index');
$routes->get('api/buzz/(:num)', 'BuzzController::show/$1');
$routes->post('api/buzz/upsert', 'BuzzController::upsert');
$routes->post('api/buzz/like/(:num)', 'BuzzController::like/$1');
$routes->delete('api/buzz/(:num)', 'BuzzController::delete/$1');

$frontRoutes = [
  '/',
  'people',
  'profile',
  'profile/(:any)',
  'account',
  'people/(:num)',
  'people_map',
  'buzz',
  'admin/users',
  'admin/invitations',
  'admin/custom_fields',
  'admin/data_editor',
  'admin/profile_data_audit',
  'admin/profile_data_audit/(:num)',
  'admin/settings',
];

foreach ($frontRoutes as $route) {
  $routes->get($route, 'Front::index');
}

$routes->get('register', 'RegisterController::registerView');
$routes->post('register', 'RegisterController::registerAction');
$routes->get('changePassword', 'Users::changePasswordView');
$routes->get('reintake', 'Users::reintakeView');
$routes->get('reintakeAccept', 'Users::reintakeAccept');
$routes->get('reintakeDeny', 'Users::reintakeDeny');
$routes->get('reintake', 'Users::reintakeView');
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
