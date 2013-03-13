<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Data\Security\Users as frameworkUsers;

/**
 * Check if the user is authenticated
 *
 * @return boolean
 */
function twig_is_authenticated() {
	global $app;
	$token = $app['security']->getToken();
	return !is_null($token);
} // twig_is_authenticated()

/**
 * Get the display name of the authenticated user
 *
 * @throws Twig_Error
 * @return string|Ambigous <unknown, string, mixed>
 */
function twig_user_display_name() {
	global $app;
	try {
		$token = $app['security']->getToken();
		if (is_null($token)) return 'ANONYMOUS';
		// get user by token
		$user = $token->getUser();
		// get the user record
		$frameworkUsers = new frameworkUsers($app);
		if (false === ($user_data = $frameworkUsers->selectUser($user->getUsername()))) {
			// user not found!
			return 'ANONYMOUS';
		}
		$display_name = (isset($user_data['displayname']) && !empty($user_data['displayname'])) ? $user_data['displayname'] : $user_data['username'];
		return $display_name;
	} catch (Exception $e) {
		throw new Twig_Error($e->getMessage());
	}
} // twig_user_display_name()

function twig_template_file($template_namespace, $template_file) {
    global $app;
    return $app['utils']->templateFile($template_namespace, $template_file);
} // twig_getTemplateFile()
