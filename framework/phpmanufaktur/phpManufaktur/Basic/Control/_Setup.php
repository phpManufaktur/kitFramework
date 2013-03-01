<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Setup\Control;

use Silex\Application;

class _Setup {
	
	protected $app = null;
	protected $request = null;
	
	/**
	 * Constructor for the Setup
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app, $request) {
		$this->app = $app;	
		$this->request = $request;
	} // __construct()
	
	public function Welcome() {
		return $this->app['translator']->trans('Welcome');
	} // Welcome()
	
		
} // class Setup
