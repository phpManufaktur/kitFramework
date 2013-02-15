<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\kitFramework;

use Silex\Application;

class kitFramework {
	
	protected $app = null;
	
	public function __construct(Application $app) {
		$this->app = $app;
	} // __construct()
	
	public function exec() {
		
		return '<b>Welcome!</b><br />This ist the kitFramework : '.__METHOD__;
	} // exec()
	
	public function exitAccessDenied() {
		$content = $this->app['twig']->render('access.denied.twig');
		return $this->app['twig']->render('body.twig', array(
				'title' => $this->app['translator']->trans('Access denied'),
				'css_file' => FRAMEWORK_URL.'/vendor/phpmanufaktur/phpManufaktur/kitFramework/View/Templates/screen.css',
				'content' => $content
				));
	} // exitAccessDenied()
	
} // class kitFramework

