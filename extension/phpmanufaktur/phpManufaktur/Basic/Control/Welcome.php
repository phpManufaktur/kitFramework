<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

class Welcome {

	protected $app;

	public function __construct() {
		global $app;
		$this->app = $app;
	} // __construct()

	public function exec() {
	    $cms = $this->app['request']->get('usage');
	    $usage = is_null($cms) ? 'framework' : $cms;
		  return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'welcome.twig'),
		      array('usage' => $usage));
	} // showDialog()

} // class Account