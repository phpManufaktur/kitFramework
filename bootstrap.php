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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\Locale;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Loader\ArrayLoader;
use phpManufaktur\kitFramework\Control\Utils;

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
	ExceptionHandler::register();
}

/**
 * Read the specified configuration file in JSON format
 * 
 * @param string $file
 * @throws \Exception
 * @return array configuration items
 */
function readConfiguration($file) {
	if (file_exists($file)) {
		if (null == ($config = json_decode(file_get_contents($file), true))) {
			$code = json_last_error();
			// get JSON error message from last error code
			switch ($code):
			case JSON_ERROR_NONE:
				$error = 'No errors'; break;
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded'; break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Underflow or the modes mismatch'; break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found'; break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON'; break;
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded'; break;
			default:
				$error = 'Unknown error'; break;
			endswitch;
			// throw Exception
			throw new \Exception(sprintf('Error decoding JSON file %s, returned error code: %d - %s',	$file, $code, $error));
		}
	}
	else {
		throw new \Exception(sprintf('Missing the configuration file: %s!', $file));
	}
	// return the configuration array
	return $config;
} // readConfiguration()


// init application
$app = new Silex\Application();
	
try {
	// check for the framework configuration file
	$framework_config = readConfiguration(__DIR__.'/config/framework.json');
	// framework constants
	define('FRAMEWORK_URL', $framework_config['FRAMEWORK_URL']);
	define('FRAMEWORK_PATH', $framework_config['FRAMEWORK_PATH']);
	define('FRAMEWORK_TEMP_PATH', isset($framework_config['FRAMEWORK_TEMP_PATH']) ? 
		$framework_config['FRAMEWORK_TEMP_PATH'] : FRAMEWORK_PATH.'/temp');
	define('MANUFAKTUR_PATH', FRAMEWORK_PATH.'/vendor/phpmanufaktur/phpManufaktur');
	define('THIRDPARTY_PATH', FRAMEWORK_PATH.'/vendor/thirdparty/thirdParty');
}
catch (\Exception $e) {
	throw new \Exception('Problem setting the framework constants!', 0, $e);	
}

// debug mode
$app['debug'] = (isset($framework_config['debug'])) ? $framework_config['debug'] : true;

// get the filesystem into the application
$app['filesystem'] = function () {
	return new Filesystem();
};

$directories = array(
		FRAMEWORK_PATH.'/logfile',
		FRAMEWORK_PATH.'/temp/cache',
		FRAMEWORK_PATH.'/temp/session'
		);

// check the needed temporary directories and create them if needed
if (!$app['filesystem']->exists($directories)) 
	$app['filesystem']->mkdir($directories);

$max_log_size = (isset($framework_config['logfile_max_size'])) ? $framework_config['logfile_max_size'] : 2*1024*1024; // 2 MB
$log_file = FRAMEWORK_PATH.'/logfile/kit2.log';
if ($app['filesystem']->exists($log_file) && (filesize($log_file) > $max_log_size)) {
	$app['filesystem']->remove(FRAMEWORK_PATH.'/logfile/kit2.log.bak');
	$app['filesystem']->rename($log_file, FRAMEWORK_PATH.'/logfile/kit2.log.bak');
}

// register monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $log_file
));
$app['monolog']->addDebug('MonologServiceProvider registered.');

try {
	// read the CMS configuration
	$cms_config = readConfiguration(FRAMEWORK_PATH.'/config/cms.json');
	// setting the CMS constants
	define('CMS_PATH', $cms_config['CMS_PATH']);
	define('CMS_URL', $cms_config['CMS_URL']);
	define('CMS_MEDIA_PATH', $cms_config['CMS_MEDIA_PATH']);
	define('CMS_MEDIA_URL', $cms_config['CMS_MEDIA_URL']);
	define('CMS_TEMP_PATH', $cms_config['CMS_TEMP_PATH']);
	define('CMS_TEMP_URL', $cms_config['CMS_TEMP_URL']);
	define('CMS_ADMIN_PATH', $cms_config['CMS_ADMIN_PATH']);
	define('CMS_ADMIN_URL', $cms_config['CMS_ADMIN_URL']);
	define('CMS_TYPE', $cms_config['CMS_TYPE']);
	define('CMS_VERSION', $cms_config['CMS_VERSION']);
} catch (\Exception $e) {
	throw new \Exception('Problem setting the CMS constants!', 0, $e); 
}
$app['monolog']->addDebug('CMS constants defined.');

try {
	// read the doctrine configuration
	$doctrine_config = readConfiguration(FRAMEWORK_PATH.'/config/doctrine.cms.json');
	define('CMS_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX']);
	define('FRAMEWORK_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX'].'kit2_');
	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
			'db.options' => array(
					'driver'   => 'pdo_mysql',
					'dbname' => $doctrine_config['DB_NAME'],
					'user' => $doctrine_config['DB_USERNAME'],
					'password' => $doctrine_config['DB_PASSWORD'],
					'host' => $doctrine_config['DB_HOST'],
					'port' => $doctrine_config['DB_PORT']
			),
	));
}
catch (\Exception $e) {
	throw new \Exception('Problem initilizing Doctrine!', 0, $e);
}
$app['monolog']->addDebug('DoctrineServiceProvider registered');

// register the session handler
$app->register(new Silex\Provider\SessionServiceProvider, array(
    'session.storage.save_path' => dirname(__DIR__) . '/temp/session'
));
$app['monolog']->addDebug('SessionServiceProvider registered.');

// register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path' => array(
				FRAMEWORK_PATH.'/vendor/phpmanufaktur/phpManufaktur/'
				),
		'twig.options' => array(
				'cache' => $app['debug'] ? false : FRAMEWORK_PATH.'/temp/cache/',
				'strict_variables' => $app['debug'] ? true : false
				)
));
$app['monolog']->addDebug('TwigServiceProvider registered.');

// quick and dirty ... to be improved!
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$langs = array();
	// break up string into pieces (languages and q factors)
	preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
	if (count($lang_parse[1]) > 0) {
		foreach ($lang_parse[1] as $lang) {
			if (false === (strpos($lang, '-'))) $locale = $lang;
			break;
		}
	}
}
else {
	$locale = 'en';
}

// register the Translator
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
		'locale' => $locale,
		'locale_fallback' => 'en',
));


$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
	$translator->addLoader('array', new ArrayLoader());	
	return $translator;
}));

$app['monolog']->addDebug('Translator Service registered. Added ArrayLoader to the Translator');

$scan_paths = array(
		MANUFAKTUR_PATH,
		THIRDPARTY_PATH
		);
// loop through /phpManufaktur and /thirdParty to include bootstrap extensions
foreach ($scan_paths as $scan_path) {
	$entries = scandir($scan_path);
	foreach ($entries as $entry) {
		if (is_dir($scan_path.'/'.$entry)) {
			if (file_exists($scan_path.'/'.$entry.'/bootstrap.include.php')) {
				// include the bootstrap extension
				include_once $scan_path.'/'.$entry.'/bootstrap.include.php';
			}
		}
	}
}

// run Silex, run ...
$app->run();
