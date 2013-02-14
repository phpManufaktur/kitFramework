<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\kitFramework\kitFramework;

// add the path to the kitFramework Twig templates
$app['twig.loader.filesystem']->addPath(
		$app['base_path'].'/vendor/phpmanufaktur/phpManufaktur/kitFramework/View/Templates'
);

// scan the /Locale directory and add all available languages
try {
	$locale_path = $app['base_path'].'/vendor/phpmanufaktur/phpManufaktur/kitFramework/Data/Locale';
	if (false === ($lang_files = scandir($locale_path)))
		throw new \Exception(sprintf("Can't read the /Locale directory %s for kitFramework!", $locale_path));
	$ignore = array('.', '..', 'index.php');
	foreach ($lang_files as $lang_file) {
		if (in_array($lang_file, $ignore)) continue;
		$lang_name = pathinfo($locale_path.'/'.$lang_file, PATHINFO_FILENAME);
		// add the locale resource file
		$app['translator']->addResource('array', $app['toolbox']->returnArrayFromFile($locale_path.'/'.$lang_file), $lang_name);		
	}
}
catch (\Exception $e) {
	throw new \Exception(sprintf('Error scanning the /Locale directory %s for kitFramework.', $locale_path), 0, $e);
}

// catch all root calls
$app->get('/', function () use ($app) {
	$framework = new kitFramework($app);
	return $framework->exec();
});