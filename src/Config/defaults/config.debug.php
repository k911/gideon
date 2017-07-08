<?php


// Root directory for application
$root = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR;

// Src directory is root directory of source code (used only for defaults configs/locales)
$src = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;
$locale = $src . 'Locale' . DIRECTORY_SEPARATOR . 'defaults' . DIRECTORY_SEPARATOR;

// Application directory in public_html folder.
// http(s)://domain.com/{$public_html}
// e.g. for `localhost/gideon` => 'gideon/'
// and for `localhost` => '' (no slash if in root public directory)
$public_html = 'gideon/';

// Useful pathes
$htdocs = $root . 'htdocs' . DIRECTORY_SEPARATOR;
$app = $root . 'application' . DIRECTORY_SEPARATOR;
$log = $app . 'log' . DIRECTORY_SEPARATOR;
$view = $app . 'view' . DIRECTORY_SEPARATOR;
$cache = $app . 'cache' . DIRECTORY_SEPARATOR;

return
[
    /**
     * Basic informations, paths, urls
     * Remarks: '//' stands for choose current browser protocol
     */
    'PUBLIC_HTML' => $public_html,
    'URL' => "//{$_SERVER['HTTP_HOST']}/$public_html",
    'CSS' => "//{$_SERVER['HTTP_HOST']}/{$public_html}css",
    'IMG' => "//{$_SERVER['HTTP_HOST']}/{$public_html}img",
    'ASSETS' => "//{$_SERVER['HTTP_HOST']}/{$public_html}assets",
    'UPLOAD' => "//{$_SERVER['HTTP_HOST']}/{$public_html}assets/upload",
    'JS' => "//{$_SERVER['HTTP_HOST']}/{$public_html}js",

    /**
     * @see Gideon\Application
     */
    'APPLICATION_CONTROLLER_PREFIX' => 'Gideon\\Controller\\',
    'APPLICATION_CLOSURE_ACTION_NAME' => 'anonymous',

    /**
     * @see Gideon\Cache\SimpleCache
     */
    'CACHE_PATH' => $cache,
    'CACHE_MODE_DIR' => 0775,
    'CACHE_MODE_FILE' => 0664,
    //'CACHE_TTL_DEFAULT' => 10*365*86400,
    //'CACHE_HASH_DEFAULT' => 'sha256',

    /**
     * @see Gideon\Database\Connection\MySQL
     */
    'MYSQL_SETTINGS_DEFAULT' =>
    [
        'host' => 'localhost',
        'dbname' => 'comp_tests',
        'username' => 'test',
        'password' => 'p5sVz8FRZvXzhMXQ',
        'charset' => 'utf8'
    ],

    /**
     * @see Gideon\Debug\Logger
     */
    'LOGGER_INIT_DEFAULT' => true,
    'LOGGER_FILE' => $log . 'debug.log',
    'LOGGER_RESET_LOG' => false,
    'LOGGER_ROOT' => 'src',
    'LOGGER_LOG_TRACES' => true,

    /**
     * @see Gideon\Handler\Locale
     */
    'LOCALE_DEFAULT' => 'en_EN',
    'LOCALE_SESSION_ID' => 'dasjs',
    'LOCALE_PATH' => $locale,

    /**
     * @see Gideon\Router\FastRouter
     */
    'FAST_ROUTER_MAX_CHUNKS' => 10,

    /**
     * @see Gideon\Router\Base
     */
    'ROUTER_REPLACEMENTS_DEFAULT' => [
        'any' => '[^/]*',           // anything pattern
        'id' => '\d+',              // decimal id
        'numeric' => '[1-9]\d*',    // decimal number without leading zero
        'hash' => '[a-fA-F\d]+',    // hexadecimal hash
        'word' => '\w+',            // not empty word expression
    ],

    /**
     * @see Gideon\Http\Request
     */
    'REQUEST_METHOD_DEFAULT' => 'GET',
    'REQUEST_METHODS_SUPPORTED' => [
        'GET',
        'POST',
        'PUT',
        'DELETE'
    ],

    /**
     * @see Gideon\Http\Response
     */
    'RESPONSE_CODE_DEFAULT' => 200,
    'RESPONSE_TYPE_DEFAULT' => 'text/plain',

    /**
     * @see Gideon\Http\Response\JSON
     */
    'JSON_TYPE_DEFAULT' => 'text/json',
    'JSON_TYPES_SUPPORTED' => [
        'application/json',
        'text/json'
    ],
    'JSON_CONTAINER_RESULT' => 'data',

    /**
     * @see Gideon\Http\Reponse\View
     */
    'VIEW_TYPE_DEFAULT' => 'text/html',
    'VIEW_DEFAULT' => 'index/index',
    'VIEW_PATH' => $view,
    'VIEW_HEADER' => $view . '_HEADER.php',
    'VIEW_FOOTER' => $view . '_FOOTER.php',
    'VIEW_ERROR' => 'error/',

    /**
     * @see Gideon\Http\Response\Text
     */
    'TEXT_REPLACE_PATTERN' => '~{{2}([A-Z]+)_([A-Z_]+)}{2}~',
    'TEXT_REPLACE_UNDEFINED' => 'undefined',

];
