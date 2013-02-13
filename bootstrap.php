<?php

/**
 * kit2
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

require_once __DIR__.'/vendor/autoload.php';

use phpManufaktur\Toolbox\Control\Toolbox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\Locale;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Loader\ArrayLoader;

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
	ExceptionHandler::register();
}

// init application
$app = new Silex\Application();

// debug mode
$app['debug'] = true;

// save the base path
$app['base_path'] = __DIR__;

// get the filesystem into the application
$app['filesystem'] = function () {
	return new Filesystem();
};

$directories = array(
		__DIR__.'/temp/logfile',
		__DIR__.'/temp/cache',
		__DIR__.'/temp/session'
		);

// check the needed temporary directories and create them if needed
if (!$app['filesystem']->exists($directories)) 
	$app['filesystem']->mkdir($directories);

$max_log_size = 2*1024*1024; // 2 MB
$log_file = __DIR__.'/temp/logfile/kit2.log';
if ($app['filesystem']->exists($log_file) && (filesize($log_file) > $max_log_size)) {
	$app['filesystem']->remove(__DIR__.'/temp/logfile/kit2.log.bak');
	$app['filesystem']->rename($log_file, __DIR__.'/temp/logfile/kit2.log.bak');
}

// register monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $log_file
));
$app['monolog']->addDebug('MonologServiceProvider registered.');

// register the session handler
$app->register(new Silex\Provider\SessionServiceProvider, array(
    'session.storage.save_path' => dirname(__DIR__) . '/temp/session'
));
$app['monolog']->addDebug('SessionServiceProvider registered.');

// register the phpManufaktur Toolbox
$app['toolbox'] = function () {
    return new Toolbox();
};

// register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path' => array(
				__DIR__.'/vendor/phpmanufaktur/phpManufaktur/'
				),
		'twig.options' => array(
				'cache' => $app['debug'] ? false : __DIR__.'/temp/cache/',
				'strict_variables' => $app['debug'] ? true : false
				)
));
$app['monolog']->addDebug('TwigServiceProvider registered.');

// register the Translator
$locale = $app['toolbox']->getLanguageFromBrowser(array('de','en'), 'de'); 

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
		'locale' => $locale,
		'locale_fallback' => 'en',
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
	$translator->addLoader('array', new ArrayLoader());	
	return $translator;
}));

$app['monolog']->addDebug('Translator Service for YAML files registered.');

// loop through the /phpManufaktur path and include bootstrap extensions
$scan_path = __DIR__.'/vendor/phpmanufaktur/phpManufaktur';
$entries = scandir($scan_path);
foreach ($entries as $entry) {
	if (is_dir($scan_path.'/'.$entry)) {
		if (file_exists($scan_path.'/'.$entry.'/bootstrap.include.php')) {
			include_once $scan_path.'/'.$entry.'/bootstrap.include.php';
		}
	}
}

// run Silex, run ...
$app->run();
