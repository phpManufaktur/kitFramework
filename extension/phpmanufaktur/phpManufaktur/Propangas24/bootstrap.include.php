<?php

/**
 * kfPropangas24
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use Symfony\Component\HttpFoundation\Request;
use phpManufaktur\Basic\Data\CMS\Users as cmsUsers;
use phpManufaktur\Propangas24\Control\Backend;

// scan the /Locale directory and add all available languages
try {
	$locale_path = MANUFAKTUR_PATH.'/Propangas24/Data/Locale';
	if (false === ($lang_files = scandir($locale_path)))
		throw new \Exception(sprintf("Can't read the /Locale directory %s!", $locale_path));
	$ignore = array('.', '..', 'index.php');
	foreach ($lang_files as $lang_file) {
		if (!is_file($locale_path.'/'.$lang_file)) continue;
		if (in_array($lang_file, $ignore)) continue;
		$lang_name = pathinfo($locale_path.'/'.$lang_file, PATHINFO_FILENAME);
		// get the array from the desired file
		$lang_array = include_once $locale_path.'/'.$lang_file;
		// add the locale resource file
		$app['translator'] = $app->share($app->extend('translator', function ($translator, $app) use ($lang_array, $lang_name) {
		    $translator->addResource('array', $lang_array, $lang_name);
		    return $translator;
		}));
	}
}
catch (\Exception $e) {
	throw new \Exception(sprintf('Error scanning the /Locale directory %s.', $locale_path));
}

// access Propangas24 with the backend of WebsiteBaker or LEPTON CMS
$app->match('/propangas24/cms/backend', function (Request $request) use ($app) {
    $json = $app['request']->get('cms');
    $params = json_decode($json, true);
    if (is_null($params) || !is_array($params)) {
        // perhaps illegal access?
        throw new \Exception('Invalid call for /propangas24/cms - missing parameters!');
    }
    // check user authentication
    $Users = new cmsUsers();
    $user_data = $Users->selectUser($params['cms']['username']);
    if (!isset($user_data['password']) || ($user_data['password'] != $params['cms']['password'])) {
        // authentication failed
        throw new \Exception('Authentication failed');
    }
    // go ahead with backend ations
    $About = new Backend();
    return $About->dlgAbout($params);
});

$app->get('/admin/propangas24/about', function (Request $request) use ($app) {
    $About = new Backend();
    return $About->dlgAbout(array());
})
->bind('propangas24_about');

$app->get('/admin/propangas24/list', function (Request $request) use ($app) {
    $List = new Backend();
    return $List->dlgList(array());
})
->bind('propangas24_list');
