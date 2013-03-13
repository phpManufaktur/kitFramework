<?php

/**
 * propangas24
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/propangas24
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Propangas24\Control;

class Backend {

    protected $app = null;

    public function __construct() {
        global $app;
        $this->app = $app;
    } // __construct()

    public function getToolbar($params, $active) {
        $toolbar_array = array(
            'list' => array(
                'name' => 'list',
                'text' => 'List',
                'link' => (isset($params['form']['action'])) ?
                    sprintf('%s&link=list', $params['form']['action']) :
                    $this->app['url_generator']->generate('propangas24_list'),
                'active' => ($active == 'list')
                ),
            'about' => array(
                'name' => 'about',
                'text' => 'About',
                'link' => (isset($params['form']['action'])) ?
                sprintf('%s&link=list', $params['form']['action']) :
                    $this->app['url_generator']->generate('propangas24_about'),
                    'active' => ($active == 'about')
                )
        );
        return $toolbar_array;
    } // getToolbar()

    public function dlgAbout($params) {
        $usage = (isset($params['cms']['type'])) ? $params['cms']['type'] : 'framework';
        $toolbar = $this->getToolbar($params, 'about');
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Propangas24/Template', 'about.backend.twig'),
            array(
                'usage' => $usage,
                'toolbar' => $toolbar
        ));
    } // dlgAbout()

    public function dlgList($params) {
        $usage = (isset($params['cms']['type'])) ? $params['cms']['type'] : 'framework';
        $toolbar = $this->getToolbar($params, 'list');
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Propangas24/Template', 'about.backend.twig'),
            array(
                'usage' => $usage,
                'toolbar' => $toolbar
            ));
    } // dlgAbout()


} // class Backend