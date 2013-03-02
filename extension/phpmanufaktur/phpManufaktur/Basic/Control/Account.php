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

class Account {

	protected $app;

	public function __construct() {
		global $app;
		$this->app = $app;
	} // __construct()

	public function showDialog() {
		return $this->app['twig']->render('@phpManufaktur/Basic/Template/Default/account.twig', array());
	} // showDialog()

} // class Account