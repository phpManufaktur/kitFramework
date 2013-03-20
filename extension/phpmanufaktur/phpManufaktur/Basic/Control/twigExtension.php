<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Twig_Extension;
use Twig_SimpleFunction;

require_once MANUFAKTUR_PATH.'/Basic/Control/twigFunction.php';

class twigExtension extends Twig_Extension {

	/**
	 * @see Twig_ExtensionInterface::getName()
	 */
	public function getName() {
		return 'kitFramework';
	} // getName()

	/**
	 * @see Twig_Extension::getGlobals()
	 */
	public function getGlobals() {
		return array(
		    'FRAMEWORK_URL' => FRAMEWORK_URL,
				'FRAMEWORK_TEMPLATE_URL' => FRAMEWORK_TEMPLATE_URL,
		    'CMS_TEMPLATE_URL' => CMS_TEMPLATE_URL,
				'MANUFAKTUR_URL' => MANUFAKTUR_URL,
				'THIRDPARTY_URL' => THIRDPARTY_URL
		);
	} // getGlobals()

	/**
	 * @see Twig_Extension::getFunctions()
	 */
	public function getFunctions() {
		return array(
				new Twig_SimpleFunction('is_authenticated', 'twig_is_authenticated'),
				new Twig_SimpleFunction('user_display_name', 'twig_user_display_name'),
		    new Twig_SimpleFunction('template_file', 'twig_template_file'),
	  );
	}	// getFunctions()

} // class twigExtension

