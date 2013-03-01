<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS;

use Silex\Application;
use phpManufaktur\Basic\Control\Utils;

class Users {
	
	protected $app = null;
	
	public function __construct(Application $app) {
		$this->app = $app;
	} // __construct()

	public function selectUser($name, &$is_admin=false) {
		try {
			$login = strtolower($name);
			$SQL = "SELECT * FROM `".CMS_TABLE_PREFIX."users` WHERE (`username`='$login' OR `email`='$login') AND `active`='1'";
			$result = $this->app['db']->fetchAssoc($SQL);
		} catch (\Doctrine\DBAL\DBALException $e) {
			throw new \Exception($e->getMessage(), 0, $e);
		}
		if (!isset($result['username']))
			return false;
		$user = array();
		foreach ($result as $key => $value)
			$user[$key] = (is_string($value)) ? Utils::unsanitizeText($value) : $value;
		$groups = explode(',', $user['groups_id']);
		$is_admin = (in_array(1, $groups));
		return $user;
	} // selectUser()
	
} // class Users