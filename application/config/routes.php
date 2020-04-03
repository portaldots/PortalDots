<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|   $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|       my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'Index_controller';
$route['404_override'] = 'Home/error_404';
$route['translate_uri_dashes'] = false;

// 回答の新規作成（企画単位で回答するフォーム）
$route["forms/(:num)/answers/create"] = "home/Applications_form/index/$1/create";

// 回答の編集（企画単位で回答するフォーム）
$route["forms/(:num)/answers/(:num)/edit"] = "home/Applications_form/index/$1/$2";

// ...企画ID/b:ブースID/forms/フォームID/タイプ => applications_form/フォームID/企画ID/タイプ/ブースID
// $route["home/applications/(:num)/b:(:num)/forms/(:num)/(:any)"] = "home/Applications_form/index/$3/$1/$4/$2";

// Uploads
$route['uploads/applications_form/(:any)'] = 'uploads/applications_form/index/$1';

// Schedules
$route['home/schedules/(:num)/(:num)/(:num)'] = 'home/schedules/index/$1/$2/$3';
$route['home/schedules/(:num)/(:num)'] = 'home/schedules/index/$1/$2';
