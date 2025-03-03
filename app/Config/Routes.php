<?php

namespace Config;

use App\Libraries\SystemSettingsWrapper;

/**
 * Summary of Config\addConditionalroutes
 * @param array $modules
 * @param callable $callback
 * @return void
 */
function addConditionalroutes(array $modules, callable $callback) {
  $enabled = false;
  if (php_sapi_name() === 'cli') {
    $enabled = true;
  } else {
    $settings = SystemSettingsWrapper::getInstance();
    foreach ($modules as $module) {
      if ($settings->isValueInArray($module, 'SystemEnabledModules')) {
        $enabled = true;
        break;
      }
    }
  }
  if ($enabled) {
    $callback();
  }
}

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('FrontController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoroutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Users routes
$routes->get('api/users/current', 'UsersController::current');
$routes->get('api/users/profileStatus', 'UsersController::profileStatus');
$routes->get('api/users/profile/(:any)', 'UsersController::profile/$1');
$routes->get('api/users/profileStructure', 'UsersController::profileStructure');
$routes->post('api/users/saveProfile', 'UsersController::saveProfile');
$routes->post('api/users/savePublicProfileStatus', 'UsersController::savePublicProfileStatus');
$routes->post('api/users/uploadProfileImage/(:any)', 'UsersController::uploadProfileImage/$1');
$routes->post('api/users/removeProfileImage/(:any)', 'UsersController::removeProfileImage/$1');
$routes->post('api/admin/users/setActiveStatus', 'UsersController::setActiveStatus');
$routes->post('api/admin/users/sync', 'UsersController::sync');
$routes->post('api/admin/users/createNewUser', 'UsersController::createNewUser');
$routes->post('api/admin/users/setActiveAffiliation', 'UsersController::setActiveAffiliation');
$routes->get('api/admin/users', 'UsersController::adminIndex');
$routes->post('api/admin/users/delete', 'UsersController::delete');
$routes->post('api/admin/users/changeRole', 'UsersController::changeRole');
$routes->post('api/admin/users/importFromCsv', 'UsersController::importFromCsv');
$routes->get('api/admin/users/csvImportTemplate', 'UsersController::getUsersCsvImportTemplate');
$routes->post('api/admin/users/setReintakeStatus', 'UsersController::setReintakeStatus');
$routes->get('changePassword', 'UsersController::changePasswordView');
$routes->get('reintake', 'UsersController::reintakeView');
$routes->get('reintakeAccept', 'UsersController::reintakeAccept');
$routes->get('reintakeDeny', 'UsersController::reintakeDeny');
$routes->post('changePassword', 'UsersController::changePassword');

// Register routes
$routes->get('register', 'RegisterController::registerView');
$routes->post('register', 'RegisterController::registerAction');

// Files routes
$routes->get('api/files/get/(.+)', 'FilesController::get/$1');

// System settings routes
$routes->get('api/admin/systemSettings', 'SystemSettingsController::index');
$routes->post('api/admin/systemSettings', 'SystemSettingsController::saveSettings');
$routes->get('api/admin/publicSystemSettings', 'SystemSettingsController::getPublicSettings');

// News routes
addConditionalroutes(['news'], function () use ($routes) {
  $routes->get('api/news', 'NewsController::index');
});

// People routes
addConditionalroutes(['people'], function () use ($routes) {
  $routes->post('api/people', 'PeopleController::index');
  $routes->get('api/people/interests', 'PeopleController::interests');
  $routes->get('api/people/(:num)', 'PeopleController::person/$1');
  $routes->get('api/people/filters', 'PeopleController::filters');
  $routes->get('api/people/export', 'PeopleController::export');
  $routes->get('api/people/exportAllData', 'PeopleController::exportAllData');
  $routes->get('api/people/indexRemote', 'PeopleController::indexRemote');
});

// Data Audit routes
addConditionalroutes(['data_audit'], function () use ($routes) {
  $routes->get('api/admin/profileDataAudit', 'DataAuditController::profileDataAudit');
  $routes->post('api/admin/profileDataAudit/process', 'DataAuditController::auditRecordProcess');
  $routes->get('api/admin/profileDataAudit/getChangesFields', 'DataAuditController::getChangesFields');
});

// Invitations routes
addConditionalroutes(['invitations'], function () use ($routes) {
  $routes->get('api/admin/invitations', 'InvitationsController::index');
  $routes->post('api/admin/invitations/upsert', 'InvitationsController::upsert');
  $routes->post('api/admin/invitations/delete', 'InvitationsController::delete');
});

// Custom fields routes
addConditionalroutes(['custom_fields'], function () use ($routes) {
  $routes->get('api/admin/customFields', 'CustomFieldsController::index');
  $routes->post('api/admin/customFields/upsert', 'CustomFieldsController::upsert');
});

// Data editor routes
addConditionalroutes(['data_editor'], function () use ($routes) {
  $routes->post('api/admin/dataEditor', 'DataEditorController::index');
  $routes->post('api/admin/dataEditor/saveItem', 'DataEditorController::saveItem');
});

// Buzz routes
addConditionalroutes(['buzz'], function () use ($routes) {
  $routes->get('api/buzz', 'BuzzController::index');
  $routes->get('api/buzz/(:num)', 'BuzzController::show/$1');
  $routes->post('api/buzz/upsert', 'BuzzController::upsert');
  $routes->post('api/buzz/like/(:num)', 'BuzzController::like/$1');
  $routes->post('api/buzz/delete/(:num)', 'BuzzController::delete/$1');
});

// Zoom scheduler routes
addConditionalroutes(['zoom_scheduler'], function () use ($routes) {
  $routes->get('api/zoom_scheduler', 'ZoomSchedulerController::index');
  $routes->post('api/zoom_scheduler', 'ZoomSchedulerController::createMeeting');
  $routes->post('api/zoom_scheduler/(:num)', 'ZoomSchedulerController::deleteMeeting/$1');
});

// Front-end Application routes
$frontroutes = [
  '/',
  'people',
  'profile',
  'profile/(:any)',
  'account',
  'people/(:num)',
  'people_map',
  'buzz',
  'zoom_scheduler',
  'admin/users',
  'admin/invitations',
  'admin/custom_fields',
  'admin/data_editor',
  'admin/profile_data_audit',
  'admin/profile_data_audit/(:num)',
  'admin/settings',
];

foreach ($frontroutes as $route) {
  $routes->get($route, 'FrontController::index');
}

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
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/routes.php';
}
