<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use phpManufaktur\Setup\Setup;
use phpManufaktur\Setup\Control\Login;


// add the path to the kitFramework Twig templates
//$app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH.'/Basic/View', 'phpManufaktur');


// scan the /Locale directory and add all available languages
try {
	$locale_path = MANUFAKTUR_PATH.'/Basic/Data/Locale';
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
		$app['translator']->addResource('array', $lang_array, $lang_name);
	}
}
catch (\Exception $e) {
	throw new \Exception(sprintf('Error scanning the /Locale directory %s.', $locale_path), 0, $e);
}

if (!file_exists(MANUFAKTUR_PATH.'/Service')) {
	// seems that the framework is not complete initialized!
	$app->match('/', function (Request $request) use ($app) {
		return new Response('Welcome!');
		$Login = new Login($app, $request);
		return $Login->Dialog();
	});

	$app->match('/admin/test', function (Request $request) use ($app) {
		$token = $app['security']->getToken();
		$user = array();
		if (null !== $token) {
			$user = $token->getUser();
		}
		print_r($user);
		//echo $user->getRoles();
		if ($app['security']->isGranted('ROLE_ADMIN')) {
			echo "hi admin!";
		}
		return new Response('Hi - admin');
	});


}

$app->match('/framework/setup', function (Request $request) use ($app) {
	$Setup = new Setup($app, $request);
	return new Response($Setup->Login());
});

$app->match('/framework/setup/welcome', function(Request $request) use ($app) {
	$Setup = new Setup($app, $request);
	return new Response($Setup->Welcome());
});
