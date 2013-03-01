<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\Security;

use Silex\Application;
use phpManufaktur\Basic\Control\Utils;

class Users {
	
	protected $app = null;
	
	public function __construct(Application $app) {
		$this->app = $app;
	} // __construct()
	
	/**
	 * Create the table 'users'
	 *
	 * @throws \Exception
	 */
	public function createTable() {
		$table = FRAMEWORK_TABLE_PREFIX.'users';
		$SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(32) NOT NULL DEFAULT '',
      `email` VARCHAR(255) NOT NULL DEFAULT '',
      `password` VARCHAR(255) NOT NULL DEFAULT '',
      `displayname` VARCHAR(64) NOT NULL DEFAULT '',
      `last_login` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `roles` VARCHAR(255) NOT NULL DEFAULT '',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`username`, `email`)
    )
    COMMENT='The user table for the kitFramework'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
		try {
			$this->app['db']->query($SQL);
			$this->app['monolog']->addDebug("Created table 'users' for the class UserProvider");
		} catch (\Doctrine\DBAL\DBALException $e) {
			throw new \Exception($e->getMessage(), 0, $e);
		}
	} // createTable()
	
	/**
	 * Select a User record from the given $name where $name can be the login name
	 * or the email address of the user
	 * 
	 * @param string $name
	 * @throws \Exception
	 * @return boolean|multitype:Ambigous <string, mixed, unknown>
	 */
	public function selectUser($name) {
		try {
			$login = strtolower(trim($name));
			$SQL = "SELECT * FROM `".FRAMEWORK_TABLE_PREFIX."users` WHERE `username`='$login' OR `email`='$login'";
			$result = $this->app['db']->fetchAssoc($SQL);
		} catch (\Doctrine\DBAL\DBALException $e) {
			throw new \Exception($e->getMessage(), 0, $e);
		}
		if (!isset($result['username'])) {
			// no user found!
			$this->app['monolog']->addDebug(sprintf('User %s not found in table _users', $name));
			return false;
		}
		$user = array();
		foreach ($result as $key => $value)
			$user[$key] = (is_string($value)) ? Utils::unsanitizeText($value) : $value;
		return $user;
	} // selectUser()
	
	public function insertUser($data, $archive_id=-1) {
		try {
			if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['roles']))
				throw new \Exception('The fields username, email, password and roles must be set!');
			$Utils = new Utils($this->app);
			if (isset($data['displayname']))
				$data['displayname'] = $Utils->sanitizeText($data['displayname']);
			if (is_array($data['roles']))
				$data['roles'] = implode(',', $data['roles']);
			$this->app['db']->insert(FRAMEWORK_TABLE_PREFIX.'users', $data);
			$archive_id = $this->app['db']->lastInsertId();
		} catch (\Doctrine\DBAL\DBALException $e) {
			throw new \Exception($e->getMessage(), 0, $e);
		}
		return true;
	} // insertUser()
	
} // class Users