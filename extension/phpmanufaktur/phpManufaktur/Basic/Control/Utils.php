<?php

/**
 * Toolbox
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

class Utils
{

    protected $app = null;

    /**
     * Constructor for the Utils
     */
    public function __construct ()
    {
        global $app;
        $this->app = $app;
    } // __construct()

    /**
     * Sanitize variables and prepare them for saving in a MySQL record
     *
     * @param mixed $item
     * @return mixed
     */
    public static function sanitizeVariable ($item)
    {
        if (! is_array($item)) {
            // undoing 'magic_quotes_gpc = On' directive
            if (get_magic_quotes_gpc())
                $item = stripcslashes($item);
            $item = self::sanitizeText($item);
        }
        return $item;
    } // sanitizeVariable()

    /**
     * Sanitize a text variable and prepare it for saving in a MySQL record
     *
     * @param string $text
     * @return string
     */
    public static function sanitizeText ($text)
    {
        $search = array(
            "<",
            ">",
            "\"",
            "'",
            "\\",
            "\x00",
            "\n",
            "\r",
            "'",
            '"',
            "\x1a"
        );
        $replace = array(
            "&lt;",
            "&gt;",
            "&quot;",
            "&#039;",
            "\\\\",
            "\\0",
            "\\n",
            "\\r",
            "\'",
            '\"',
            "\\Z"
        );
        return str_replace($search, $replace, $text);
    } // sanitizeText()

    /**
     * Unsanitize a text variable and prepare it for output
     *
     * @param string $text
     * @return string
     */
    public static function unsanitizeText($text)
    {
        $text = stripcslashes($text);
        $text = str_replace(array(
            "&lt;",
            "&gt;",
            "&quot;",
            "&#039;"
        ), array(
            "<",
            ">",
            "\"",
            "'"
        ), $text);
        return $text;
    } // unsanitizeText()

    /**
     * Generate a globally unique identifier (GUID)
     * Uses COM extension under Windows otherwise
     * create a random GUID in the same style
     *
     * @return string $guid
     */
    public static function createGUID ()
    {
        if (function_exists('com_create_guid')) {
            $guid = com_create_guid();
            $guid = strtolower($guid);
            if (strpos($guid, '{') == 0) {
                $guid = substr($guid, 1);
            }
            if (strpos($guid, '}') == strlen($guid) - 1) {
                $guid = substr($guid, 0, strlen($guid) - 2);
            }
            return $guid;
        } else {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        }
    } // createGUID()

    /**
     * Check a password for length, chars, special chars and return a strength
     * value between 1 to 10.
     *
     * @param string $password
     * @return number
     * @link http://www.phpro.org/examples/Password-Strength-Tester.html
     */
    function passwordStrength ($password)
    {
        if (strlen($password) == 0) {
            return 1;
        }
        if (strpos($password, ' ') !== false) {
            return 1;
        }

        $strength = 0;

        // get the length of the password
        $length = strlen($password);

        // check if password is not all lower case
        if (strtolower($password) != $password) {
            $strength += 1;
        }

        // check if password is not all upper case
        if (strtoupper($password) == $password) {
            $strength += 1;
        }

        // check string length is 8 -15 chars
        if ($length >= 8 && $length <= 15) {
            $strength += 1;
        }

        // check if lenth is 16 - 35 chars
        if ($length >= 16 && $length <= 35) {
            $strength += 2;
        }

        // check if length greater than 35 chars
        if ($length > 35) {
            $strength += 3;
        }

        // get the numbers in the password
        preg_match_all('/[0-9]/', $password, $numbers);
        $strength += count($numbers[0]);

        // check for special chars
        preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
        $strength += sizeof($specialchars[0]);

        // get the number of unique chars
        $chars = str_split($password);
        $num_unique_chars = sizeof(array_unique($chars));
        $strength += $num_unique_chars * 2;

        // strength is a number 1-10
        $strength = $strength > 99 ? 99 : $strength;
        $strength = floor($strength / 10 + 1);

        return $strength;
    } // passwordStrength()


    public function templateFile ($template_namespace, $template_file)
    {
        global $TEMPLATE_NAMESPACES;

        if ($template_namespace[0] != '@') {
            throw new \Exception('Namespace expected in variable $template_namespace but path found!');
        }
        // no trailing slash!
        if (strrpos($template_namespace, '/') == strlen($template_namespace) - 1)
            $template_namespace = substr($template_namespace, 0, strlen($template_namespace) - 1);
            // separate the namespace
        if (false === strpos($template_namespace, '/')) {
            // only namespace - no subdirectory!
            $namespace = substr($template_namespace, 1);
            $directory = '';
        } else {
            $namespace = substr($template_namespace, 1, strpos($template_namespace, '/') - 1);
            $directory = substr($template_namespace, strpos($template_namespace, '/'));
        }

        // no leading slash for the template file
        if ($template_file[0] == '/')
            $template_file = substr($template_file, 1);
            // explode the template names
        $template_names = explode(',', FRAMEWORK_TEMPLATES);
        // walk through the template names
        foreach ($template_names as $name) {
            $file = $TEMPLATE_NAMESPACES[$namespace] . $directory . '/' . $name . '/' . $template_file;
            if (file_exists($file)) {
                // success - build the path for Twig
                return $template_namespace . '/' . $name . '/' . $template_file;
            }
        }
        // Uuups - no template found!
        throw new \Exception(sprintf('Template file %s not found within the namespace %s!', $template_file, $template_namespace));
    } // templateFile()

    /**
     * Formatiert einen BYTE Wert in einen lesbaren Wert und gibt
     * einen Byte, KB, MB oder GB String zurueck
     *
     * @param integer $byte
     * @return string
     */
    public static function bytes2string ($byte)
    {
        if ($byte < 1024) {
            $result = round($byte, 2) . ' Byte';
        } elseif ($byte >= 1024 and $byte < pow(1024, 2)) {
            $result = round($byte / 1024, 2) . ' KB';
        } elseif ($byte >= pow(1024, 2) and $byte < pow(1024, 3)) {
            $result = round($byte / pow(1024, 2), 2) . ' MB';
        } elseif ($byte >= pow(1024, 3) and $byte < pow(1024, 4)) {
            $result = round($byte / pow(1024, 3), 2) . ' GB';
        } elseif ($byte >= pow(1024, 4) and $byte < pow(1024, 5)) {
            $result = round($byte / pow(1024, 4), 2) . ' TB';
        } elseif ($byte >= pow(1024, 5) and $byte < pow(1024, 6)) {
            $result = round($byte / pow(1024, 5), 2) . ' PB';
        } elseif ($byte >= pow(1024, 6) and $byte < pow(1024, 7)) {
            $result = round($byte / pow(1024, 6), 2) . ' EB';
        }
        return $result;
    } // bytes2string()

    /**
     * fixes a path by removing //, /../ and other things
     *
     * @access public
     * @param string $path
     *            - path to fix
     * @return string
     *
     */
    public static function sanitizePath ($path)
    {
        // remove / at end of string; this will make sanitizePath fail otherwise!
        $path = preg_replace('~/{1,}$~', '', $path);

        // make all slashes forward
        $path = str_replace('\\', '/', $path);

        // bla/./bloo ==> bla/bloo
        $path = preg_replace('~/\./~', '/', $path);

        // resolve /../
        // loop through all the parts, popping whenever there's a .., pushing otherwise.
        $parts = array();
        foreach (explode('/', preg_replace('~/+~', '/', $path)) as $part) {
            if ($part === ".." || $part == '') {
                array_pop($parts);
            } elseif ($part != "") {
                $parts[] = $part;
            }
        }

        $new_path = implode("/", $parts);

        // windows
        if (! preg_match('/^[a-z]\:/i', $new_path)) {
            $new_path = '/' . $new_path;
        }

        return $new_path;
    } // sanitizePath()

    /**
     * Convert a german formatted number string into a valid float val
     *
     * @param string $str
     * @return number
     * @link http://www.rither.de/a/informatik/php-beispiele/strings/string-in-float-umwandeln/
     */
    public static function str2float ($str)
    {
        $pos = strrpos($str = strtr(trim(strval($str)), ',', '.'), '.');
        return ($pos === false ? floatval($str) : floatval(str_replace('.', '', substr($str, 0, $pos)) . substr($str, $pos)));
    }

} // class Utils