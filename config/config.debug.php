<?php

// Application directory
$app = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'application') . DIRECTORY_SEPARATOR;

// Root directory
$root = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

// Public directory root
$htdocs = $root . 'htdocs' . DIRECTORY_SEPARATOR;

// Site alias with ending '/' if not null
$site_alias = 'comp/';

// Useful pathes
$log = $app . 'log' . DIRECTORY_SEPARATOR;
$view = $app . 'view' . DIRECTORY_SEPARATOR;
$cache = $app . 'cache' . DIRECTORY_SEPARATOR;
$locale = $root . 'locale' . DIRECTORY_SEPARATOR;

return
[
    /**
     * Basic informations, paths, urls
     * Remarks: '//' stands for choose current browser protocol
     */
    'ALIAS' => $site_alias,
    'URL' => "//{$_SERVER['HTTP_HOST']}/$site_alias",
    'CSS' => "//{$_SERVER['HTTP_HOST']}/{$site_alias}css",
    'IMG' => "//{$_SERVER['HTTP_HOST']}/{$site_alias}img",
    'ASSETS' => "//{$_SERVER['HTTP_HOST']}/{$site_alias}assets",
    'UPLOAD' => "//{$_SERVER['HTTP_HOST']}/{$site_alias}assets/upload",
    'JS' => "//{$_SERVER['HTTP_HOST']}/{$site_alias}js",

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
     * @see Gideon\Handler\Throwable
     */
    'THROWABLE_SESSION_ARRAY' => 'thrwaos',

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
     * @see Gideon\Renderer\Response
     */
    'RESPONSE_CODE_DEFAULT' => 200,
    'RESPONSE_TYPE_DEFAULT' => 'text/plain',

    /**
     * @see Gideon\Renderer\Response\JSON
     */
    'JSON_TYPE_DEFAULT' => 'text/json',
    'JSON_TYPES_SUPPORTED' => [
        'application/json',
        'text/json'
    ],
    'JSON_CONTAINER_RESULT' => 'data',

    /**
     * @see Gideon\Renderer\Reponse\View
     */
    'VIEW_TYPE_DEFAULT' => 'text/html',
    'VIEW_DEFAULT' => 'index/index',
    'VIEW_PATH' => $view,
    'VIEW_HEADER' => $view . '_HEADER.php',
    'VIEW_FOOTER' => $view . '_FOOTER.php',
    'VIEW_ERROR' => 'error/',

    /**
     * @see Gideon\Renderer\Response\Text
     */
    'TEXT_REPLACE_PATTERN' => '~{{2}([A-Z]+)_([A-Z_]+)}{2}~',
    'TEXT_REPLACE_UNDEFINED' => 'undefined',

];
