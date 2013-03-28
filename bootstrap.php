<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
require_once __DIR__ . '/framework/autoload.php';

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Loader\ArrayLoader;
use phpManufaktur\Basic\Control\UserProvider;
use phpManufaktur\Basic\Control\manufakturPasswordEncoder;
use phpManufaktur\Basic\Control\twigExtension;
use phpManufaktur\Basic\Control\Account;
use phpManufaktur\Basic\Data\Security\Users as frameworkUsers;
use phpManufaktur\Basic\Control\forgottenPassword;
use phpManufaktur\Basic\Control\Utils;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\Welcome;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


// set the error handling

ini_set('display_errors', 1);
error_reporting(- 1);
ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    ExceptionHandler::register();
}

/**
 * Read the specified configuration file in JSON format
 *
 * @param string $file
 * @throws \Exception
 * @return array configuration items
 */
function readConfiguration ($file)
{
    if (file_exists($file)) {
        if (null == ($config = json_decode(file_get_contents($file), true))) {
            $code = json_last_error();
            // get JSON error message from last error code
            switch ($code) :
                case JSON_ERROR_NONE:
                    $error = 'No errors';
                    break;
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error = 'Unknown error';
                    break;
            endswitch;
            // throw Exception
            throw new \Exception(sprintf('Error decoding JSON file %s, returned error code: %d - %s', $file, $code, $error));
        }
    } else {
        throw new \Exception(sprintf('Missing the configuration file: %s!', $file));
    }
    // return the configuration array
    return $config;
} // readConfiguration()

// init application
$app = new Silex\Application();

try {
    // check for the framework configuration file
    $framework_config = readConfiguration(__DIR__ . '/config/framework.json');
    // framework constants
    define('FRAMEWORK_URL', $framework_config['FRAMEWORK_URL']);
    define('FRAMEWORK_PATH', $framework_config['FRAMEWORK_PATH']);
    define('FRAMEWORK_TEMP_PATH', isset($framework_config['FRAMEWORK_TEMP_PATH']) ? $framework_config['FRAMEWORK_TEMP_PATH'] : FRAMEWORK_PATH . '/temp');
    define('FRAMEWORK_TEMP_URL', isset($framwework_config['FRAMEWORK_TEMP_URL']) ? $framework_config['FRAMEWORK_TEMP_URL'] : FRAMEWORK_URL . '/temp');
    define('FRAMEWORK_TEMPLATES', isset($framework_config['FRAMEWORK_TEMPLATES']) ? $framework_config['FRAMEWORK_TEMPLATES'] : 'default');
    define('MANUFAKTUR_PATH', FRAMEWORK_PATH . '/extension/phpmanufaktur/phpManufaktur');
    define('MANUFAKTUR_URL', FRAMEWORK_URL . '/extension/phpmanufaktur/phpManufaktur');
    define('THIRDPARTY_PATH', FRAMEWORK_PATH . '/extension/thirdparty/thirdParty');
    define('THIRDPARTY_URL', FRAMEWORK_URL . '/extension/thirdparty/thirdParty');
    define('FRAMEWORK_TEMPLATE_PATH', FRAMEWORK_PATH . '/template/framework');
    define('FRAMEWORK_TEMPLATE_URL', FRAMEWORK_URL . '/template/framework');
    define('CMS_TEMPLATE_PATH', FRAMEWORK_PATH . '/template/cms');
    define('CMS_TEMPLATE_URL', FRAMEWORK_URL . '/template/cms');
    define('CONNECT_CMS_USERS', isset($framework_config['CONNECT_CMS_USERS']) ? $framework_config['CONNECT_CMS_USERS'] : true);
    define('FRAMEWORK_SETUP', isset($framework_config['FRAMEWORK_SETUP']) ? $framework_config['FRAMEWORK_SETUP'] : true);
} catch (\Exception $e) {
    throw new \Exception('Problem setting the framework constants!', 0, $e);
}

// debug mode
$app['debug'] = (isset($framework_config['DEBUG'])) ? $framework_config['DEBUG'] : false;

// get the filesystem into the application
$app['filesystem'] = function  ()
{
    return new Filesystem();
};

$directories = array(
    FRAMEWORK_PATH . '/logfile',
    FRAMEWORK_PATH . '/temp/cache',
    FRAMEWORK_PATH . '/temp/session'
);

// check the needed temporary directories and create them if needed
if (! $app['filesystem']->exists($directories))
    $app['filesystem']->mkdir($directories);

$max_log_size = (isset($framework_config['LOGFILE_MAX_SIZE'])) ? $framework_config['LOGFILE_MAX_SIZE'] : 2 * 1024 * 1024; // 2 MB
$log_file = FRAMEWORK_PATH . '/logfile/kit2.log';
if ($app['filesystem']->exists($log_file) && (filesize($log_file) > $max_log_size)) {
    $app['filesystem']->remove(FRAMEWORK_PATH . '/logfile/kit2.log.bak');
    $app['filesystem']->rename($log_file, FRAMEWORK_PATH . '/logfile/kit2.log.bak');
}

// register monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $log_file
));
$app['monolog']->addDebug('MonologServiceProvider registered.');

try {
    // read the CMS configuration
    $cms_config = readConfiguration(FRAMEWORK_PATH . '/config/cms.json');
    // setting the CMS constants
    define('CMS_PATH', $cms_config['CMS_PATH']);
    define('CMS_URL', $cms_config['CMS_URL']);
    define('CMS_MEDIA_PATH', $cms_config['CMS_MEDIA_PATH']);
    define('CMS_MEDIA_URL', $cms_config['CMS_MEDIA_URL']);
    define('CMS_TEMP_PATH', $cms_config['CMS_TEMP_PATH']);
    define('CMS_TEMP_URL', $cms_config['CMS_TEMP_URL']);
    define('CMS_ADMIN_PATH', $cms_config['CMS_ADMIN_PATH']);
    define('CMS_ADMIN_URL', $cms_config['CMS_ADMIN_URL']);
    define('CMS_TYPE', $cms_config['CMS_TYPE']);
    define('CMS_VERSION', $cms_config['CMS_VERSION']);
} catch (\Exception $e) {
    throw new \Exception('Problem setting the CMS constants!', 0, $e);
}
$app['monolog']->addDebug('CMS constants defined.');

try {
    // read the doctrine configuration
    $doctrine_config = readConfiguration(FRAMEWORK_PATH . '/config/doctrine.cms.json');
    define('CMS_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX']);
    define('FRAMEWORK_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX'] . 'kit2_');
    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver' => 'pdo_mysql',
            'dbname' => $doctrine_config['DB_NAME'],
            'user' => $doctrine_config['DB_USERNAME'],
            'password' => $doctrine_config['DB_PASSWORD'],
            'host' => $doctrine_config['DB_HOST'],
            'port' => $doctrine_config['DB_PORT']
        )
    ));
} catch (\Exception $e) {
    throw new \Exception('Problem initilizing Doctrine!', 0, $e);
}
$app['monolog']->addDebug('DoctrineServiceProvider registered');

// register the session handler
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => dirname(__DIR__) . '/temp/session',
    'session.storage.options' => array(
        'cookie_lifetime' => 0
    )
));
$app['monolog']->addDebug('SessionServiceProvider registered.');

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['monolog']->addDebug('UrlGeneratorServiceProvider registered.');

// default language
$locale = 'en';
// quick and dirty ... try to detect the favorised language - to be improved!
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = array();
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
    if (count($lang_parse[1]) > 0) {
        foreach ($lang_parse[1] as $lang) {
            if (false === (strpos($lang, '-'))) {
                // only the country sign like 'de'
                $locale = strtolower($lang);
            } else {
                // perhaps something like 'de-DE'
                $locale = strtolower(substr($lang, 0, strpos($lang, '-')));
            }
            break;
        }
    }
}

// register the Translator
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
    'locale' => $locale,
    'locale_fallback' => 'en'
));

$app['translator'] = $app->share($app->extend('translator', function  ($translator, $app)
{
    $translator->addLoader('array', new ArrayLoader());
    return $translator;
}));
$app['monolog']->addDebug('Translator Service registered. Added ArrayLoader to the Translator');

// register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => $app['debug'] ? false : FRAMEWORK_PATH . '/temp/cache/',
        'strict_variables' => $app['debug'] ? true : false,
        'debug' => $app['debug'] ? true : false,
        'autoescape' => false
    )
));

// set namespaces for phpManufaktur, thirdParty, framework and CMS template
$app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpManufaktur');
$app['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdParty');
$app['twig.loader.filesystem']->addPath(FRAMEWORK_TEMPLATE_PATH, 'framework');
$app['twig.loader.filesystem']->addPath(CMS_TEMPLATE_PATH, 'CMS');

// important for $app['utils']->templateFile()
$TEMPLATE_NAMESPACES = array(
    'phpManufaktur' => MANUFAKTUR_PATH,
    'thirdParty' => THIRDPARTY_PATH,
    'framework' => FRAMEWORK_TEMPLATE_PATH,
    'cms' => CMS_TEMPLATE_PATH
);

$app['twig'] = $app->share($app->extend('twig', function  ($twig, $app)
{
    // add global variables, functions etc. for the templates
    $twig->addExtension(new twigExtension());
    if ($app['debug']) {
        $twig->addExtension(new Twig_Extension_Debug());
    }
    return $twig;
}));

$app['monolog']->addDebug('TwigServiceProvider registered.');

// register Validator Service
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app['monolog']->addDebug('Validator Service Provider registered.');

// register the FormServiceProvider
$app->register(new Silex\Provider\FormServiceProvider());
$app['monolog']->addDebug('Form Service registered.');

// register the HTTP Cache Service
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => FRAMEWORK_PATH . '/temp/cache/'
));
$app['monolog']->addDebug('HTTP Cache Service registered.');

// register the SwiftMailer
try {
    $swift_config = readConfiguration(__DIR__ . '/config/swift.cms.json');
    $app->register(new Silex\Provider\SwiftmailerServiceProvider());
    $app['swiftmailer.options'] = array(
        'host' => isset($swift_config['SMTP_HOST']) ? $swift_config['SMTP_HOST'] : 'localhost',
        'port' => isset($swift_config['SMTP_PORT']) ? $swift_config['SMTP_PORT'] : '25',
        'username' => $swift_config['SMTP_USERNAME'],
        'password' => $swift_config['SMTP_PASSWORD'],
        'encryption' => isset($swift_config['SMTP_ENCRYPTION']) ? $swift_config['SMTP_ENCRYPTION'] : null,
        'auth_mode' => isset($swift_config['SMTP_AUTH_MODE']) ? $swift_config['SMTP_AUTH_MODE'] : null
    );
    define('SERVER_EMAIL_ADDRESS', $swift_config['SERVER_EMAIL']);
    define('SERVER_EMAIL_NAME', $swift_config['SERVER_NAME']);
    $app['monolog']->addDebug('SwiftMailer Service registered');
} catch (\Exception $e) {
    throw new \Exception('Problem initilizing the SwiftMailer!');
}

// register the Framework Utils
$app['utils'] = $app->share(function() {
    return new Utils();
});
$app['monolog']->addDebug('Framework Utils registered');

if (FRAMEWORK_SETUP) {
    // create the user table for the service provider
    try {
        $Users = new frameworkUsers();
        $Users->createTable();
    } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
    }
}

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/admin',
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/admin/login_check'
            ),
            'users' => $app->share(function  () use( $app)
            {
                return new UserProvider($app);
            }),
            'logout' => array(
                'logout_path' => '/admin/logout'
            )
        )
    ),
    'security.encoder.digest' => $app->share(function  ($app)
    {
        return new manufakturPasswordEncoder();
    })
));


$app->get('/login', function (Request $request) use($app)
{
    return $app['twig']->render($app['utils']->templateFile('@phpManufaktur/Basic/Template', 'login.twig'), array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/password/forgotten', function () use($app)
{
    // user has forgot the password
    $forgotPassword = new forgottenPassword();
    return $forgotPassword->dialogForgottenPassword();
});

$app->match('/password/reset', function (Request $request) use ($app)
{
    // send the user a GUID to reset the password
    $resetPassword = new forgottenPassword();
    return $resetPassword->dialogResetPassword();
});

$app->match('/password/retype', function (Request $request) use ($app) {
    $retypePassword = new forgottenPassword();
    return $retypePassword->dialogRetypePassword();
});

$app->get('/password/create/{guid}', function ($guid) use ($app)
{
    // validate the GUID and create a new password
    $createPassword = new forgottenPassword();
    return $createPassword->dialogCreatePassword($guid);
});

$app->get('/admin/account', function  (Request $request) use( $app)
{
    // user the user account dialog
    $account = new Account();
    return $account->showDialog();
});

$scan_paths = array(
    MANUFAKTUR_PATH,
    THIRDPARTY_PATH
);
// loop through /phpManufaktur and /thirdParty to include bootstrap extensions
foreach ($scan_paths as $scan_path) {
    $entries = scandir($scan_path);
    foreach ($entries as $entry) {
        if (is_dir($scan_path . '/' . $entry)) {
            if (file_exists($scan_path . '/' . $entry . '/bootstrap.include.php')) {
                // include the bootstrap extension
                include_once $scan_path . '/' . $entry . '/bootstrap.include.php';
            }
        }
    }
}

// catch all kitCommands
$app->match('/command/{command}/{params}', function (Request $request, $command, $params) use ($app) {
    try {
        $subRequest = Request::create('/cmd/'.$command.'/'.$params, 'GET');
        // important: we dont want that the app handle catch errors, so set the third parameter to false!
        $result = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
    } catch (\Exception $e) {
        $result = "~~ <b>Error</b>: Unknown kitCommand: <i>$command</i> ~~";
    }
    return $result;
});



$app->get('/', function(Request $request) use ($app) {
    $subRequest = Request::create('/welcome', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

$app->get('/admin', function(Request $request) use ($app) {
    $subRequest = Request::create('/welcome', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

$app->get('/admin/welcome', function (Request $request) use ($app) {
    $Welcome = new Welcome();
    return $Welcome->exec();
});

$app->match('/welcome', function (Request $request) use ($app) {
    $Welcome = new Welcome();
    return $Welcome->exec();
});

$app->match('/welcome/cms/{cms}', function ($cms) use ($app) {
    // get the CMS info parameters
    $cms = json_decode(base64_decode($cms), true);

    // save them partial into session
    $app['session']->set('CMS_TYPE', $cms['type']);
    $app['session']->set('CMS_VERSION', $cms['version']);
    $app['session']->set('CMS_LOCALE', $cms['locale']);
    $app['session']->set('CMS_USER_NAME', $cms['username']);

    // auto login into the admin area and then exec the welcome dialog
    $secureAreaName = 'admin';
    // @todo the access control is very soft and the ROLE is actually not checked!
    $user = new User($cms['username'],'', array('ROLE_ADMIN'), true, true, true, true);
    $token = new UsernamePasswordToken($user, null, $secureAreaName, $user->getRoles());
    $app['security']->setToken($token);
    $app['session']->set('_security_'.$secureAreaName, serialize($token) );

    $usage = ($cms['target'] == 'cms') ? $cms['type'] : 'framework';

    // sub request to the welcome dialog
    $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => $usage));
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

if (FRAMEWORK_SETUP) {
    // the setup flag was set to TRUE, now we assume that we can set it to FALSE
    $framework_config['FRAMEWORK_SETUP'] = false;
    if (! file_put_contents(__DIR__ . '/config/framework.json', json_encode($framework_config)))
        throw new \Exception('Can\'t write the configuration file for the framework!');
    return true;
}

if ($app['debug'])
    $app->run();
else
    $app['http_cache']->run();
