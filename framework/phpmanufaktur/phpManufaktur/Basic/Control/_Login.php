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
use phpManufaktur\Setup\Control\Setup;

class _Login {
	
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
	
	public function Dialog() {
		
		$form = $this->app['form.factory']->createBuilder('form')
			->add('name', 'text', array('label' => $this->app['translator']->trans('Username')))
			->add('pass', 'password', array('label' => $this->app['translator']->trans('Password')))
			->getForm();
		
		if ('POST' == $this->request->getMethod()) {
			$form->bind($this->request);
			if ($form->isValid()) {
				// get the form data
				$data = $form->getData();
				// do something with the data
		
				$Setup = new Setup($this->app, $this->request);
				return $Setup->Welcome();
			}
		}
		// display the form
		$content = $this->app['twig']->render('login.twig', array('form' => $form->createView()));
		$data = array(
				'title' => 'Login',
				'css_file' => FRAMEWORK_URL.'/vendor/phpmanufaktur/phpManufaktur/Setup/View/Templates/screen.css',
				'content' => $content
				);
		return $this->app['twig']->render('body.twig', $data);
	} // Dialog()
	
} // class Login
