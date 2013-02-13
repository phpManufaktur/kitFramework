<?php

/**
 * appEtizer
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\appEtizer\test;

$app['twig.loader.filesystem']->addPath(
	$app['base_path'].'/vendor/phpmanufaktur/phpManufaktur/appEtizer/View'
);

$app['translator']->addResource('array', $app['toolbox']->returnArrayFromFile($app['base_path'].'/vendor/phpmanufaktur/phpManufaktur/appEtizer/Data/Locale/en.php'), 'en');
$app['translator']->addResource('array', $app['toolbox']->returnArrayFromFile($app['base_path'].'/vendor/phpmanufaktur/phpManufaktur/appEtizer/Data/Locale/de.php'), 'de');

$app->get('/app', function () use ($app) {
	$test = new test($app);
	return $test->exec();
});