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


use phpManufaktur\Basic\Control\manufakturPasswordEncoder;

class Users
{

    protected $app = null;

    private static $guid_wait_hours_between_resets = 24;

    public function __construct ()
    {
        global $app;
        $this->app = $app;
    } // __construct()

    /**
     *
     * @return the $guid_wait_hours_between_resets
     */
    public static function getGuidWaitHoursBetweenResets ()
    {
        return Users::$guid_wait_hours_between_resets;
    }

    /**
     *
     * @param number $guid_wait_hours_between_resets
     */
    public static function setGuidWaitHoursBetweenResets ($guid_wait_hours_between_resets)
    {
        Users::$guid_wait_hours_between_resets = $guid_wait_hours_between_resets;
    }

    /**
     * Create the table 'users'
     *
     * @throws \Exception
     */
    public function createTable ()
    {
        $table = FRAMEWORK_TABLE_PREFIX . 'users';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(32) NOT NULL DEFAULT '',
      `email` VARCHAR(255) NOT NULL DEFAULT '',
      `password` VARCHAR(255) NOT NULL DEFAULT '',
      `displayname` VARCHAR(64) NOT NULL DEFAULT '',
      `last_login` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `roles` VARCHAR(255) NOT NULL DEFAULT '',
    	`guid` VARCHAR(255) NOT NULL DEFAULT '',
      `guid_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      `guid_status` ENUM('ACTIVE', 'LOCKED') NOT NULL DEFAULT 'ACTIVE',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`username`, `email`, `guid`)
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
     * @return boolean multitype:Ambigous mixed, unknown>
     */
    public function selectUser ($name)
    {
        try {
            $login = strtolower(trim($name));
            $SQL = "SELECT * FROM `" . FRAMEWORK_TABLE_PREFIX . "users` WHERE `username`='$login' OR `email`='$login'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        if (! isset($result['username'])) {
            // no user found!
            $this->app['monolog']->addDebug(sprintf('User %s not found in table _users', $name));
            return false;
        }
        $user = array();
        foreach ($result as $key => $value)
            $user[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
        return $user;
    } // selectUser()

    /**
     * Insert a new User into the table.
     * Create a GUID if none exists.
     *
     * @param unknown $data
     * @param unknown $archive_id
     * @throws \Exception
     * @return boolean
     */
    public function insertUser ($data, $archive_id = -1)
    {
        try {
            if (! isset($data['username']) || ! isset($data['email']) || ! isset($data['password']) || ! isset($data['roles']))
                throw new \Exception('The fields username, email, password and roles must be set!');
            if (! isset($data['guid']) || empty($data['guid'])) {
                // create a GUID and set the timestamp
                $data['guid'] = $this->app['utils']->createGUID();
                $data['guid_timestamp'] = date('Y-m-d H:i:s');
            }
            if (isset($data['displayname']))
                $data['displayname'] = $this->app['utils']->sanitizeText($data['displayname']);
            if (is_array($data['roles']))
                $data['roles'] = implode(',', $data['roles']);
            $data['email'] = strtolower($data['email']);
            // insert a new record
            $this->app['db']->insert(FRAMEWORK_TABLE_PREFIX . 'users', $data);
            $archive_id = $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        return true;
    } // insertUser()

    /**
     * Create a new GUID for the user.
     * The GUID is needed to request a new password.
     *
     * @param string $email
     * @param boolean $guid_check
     * @throws \Exception
     * @return boolean
     */
    public function createNewGUID ($email, $guid_check = true)
    {
        try {
            $user = $this->selectUser($email);
            if (! is_array($user) || ! isset($user['id'])) {
                // user does not exists - logfile is written by selectUser()
                return false;
            }
            if ($guid_check) {
                $d = strtotime($user['guid_timestamp']);
                $limit = mktime(date('H', $d) + self::getGuidWaitHoursBetweenResets(),
                    date('i', $d), date('s', $d), date('m', $d), date('d', $d), date('Y', $d));
                if (time() < $limit) {
                    // cannot create a new GUID as long the old GUID is not expired
                    return false;
                    //throw new \Exception(sprintf('Can\'t create a new GUID as long the last GUID is not expired. You must wait %d hours between the creation.'));
                }
            }
            $data = array(
                'guid' => $this->app['utils']->createGUID(),
                'guid_timestamp' => date('Y-m-d H:i:s')
            );
            $where = array(
                'email' => $email
            );
            $this->app['db']->update(FRAMEWORK_TABLE_PREFIX.'users', $data, $where);
            $this->app['monolog']->addDebug(sprintf('Created a new GUID for user %s.', $email));
            // return the GUID data array
            return $data;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // createNewGUID()

    /**
     * Select a user by the submitted GUID
     *
     * @param string $guid
     * @throws \Exception
     * @return boolean|multitype:Ambigous <string, mixed, unknown>
     */
    public function selectUserByGUID($guid) {
        try {
            $SQL = "SELECT * FROM `".FRAMEWORK_TABLE_PREFIX."users` WHERE `guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        if (!is_array($result) || !isset($result['guid'])) {
            return false;
        }
        try {
            $user = array();
            foreach ($result as $key => $value)
                $user[$key] = (is_string($value)) ? $this->app['utils']->unsanitizeText($value) : $value;
            return $user;
        } catch (\Doctrine\DBAL\DBALException $e)  {
            throw new \Exception($e->getMessage());
        }
    } // selectUserByGUID()

    /**
     * Update the record for $username with the given $data
     *
     * @param string $username
     * @param array $data
     * @throws \Exception
     */
    public function updateUser($username, $data) {
        try {
            $where = array('username' => $username);
            $update = array();
            foreach ($data as $key => $value)
                $update[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            $this->app['db']->update(FRAMEWORK_TABLE_PREFIX.'users', $update, $where);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // updateUser()

    public function existsUser($username) {
        try {
            $SQL = "SELECT `id` FROM `".FRAMEWORK_TABLE_PREFIX."users` WHERE `username`='$username' OR `email`='$username'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        return (!is_array($result) || !isset($result['id'])) ? false : true;
    } // existsUser()

    public function checkLogin($username, $password, $roles=array())
    {
        try {
            $passwordEncoder = new manufakturPasswordEncoder();
            $pass = $passwordEncoder->encodePassword($password, '');
            $SQL = "SELECT `roles` FROM `".FRAMEWORK_TABLE_PREFIX."users` WHERE (`username`='$username' OR `email`='$username') AND `password`='$pass'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        if (!is_array($result) || !isset($result['roles'])) {
            return false;
        }
        $roles = explode(',', $result['roles']);
        return true;
    }

} // class Users