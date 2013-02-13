<?php

namespace phpManufaktur\appEtizer;

use Silex\Application;

class test {
	
	protected $request = null;
	protected $app = null;
	
	public function __construct(Application $app) {
		$this->app = $app;	
	}
	
	public function exec() {
		
		return $this->app['twig']->render('test.twig', array(
				'title' => 'Ein Seitentitel',
				'css_url' => 'http://test.dev.phpmanufaktur.de/silex/vendor/phpmanufaktur/phpManufaktur/appEtizer/View/test.css',
				'name' => 'Ralf'
				));
		
		return $this->app['translator']->trans('How do you do, <b>%name%</b>?', array('%name%' => 'Ralf'));
		return $this->app['base_path'];
		return $this->app['toolbox']->generatePassword();
		
		return "ok!!!";
	}
	
}