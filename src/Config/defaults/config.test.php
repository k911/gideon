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
     */
    'PUBLIC_HTML' => $public_html,

    /**
     * @see Gideon\Application
     */
    'APPLICATION_CONTROLLER_PREFIX' => 'Gideon\\Controller\\',
    'APPLICATION_CLOSURE_ACTION_NAME' => 'anonymous',

    /**
     * @see Gideon\Cache\SimpleCache
     */
    'CACHE_PATH' => $cache . 'test' . DIRECTORY_SEPARATOR,
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
        'username' => 'comp_tester',
        'password' => 'V8OPYeZhs5Xt1GHp',
        'charset' => 'utf8'
    ],

    /**
     * @see Gideon\Debug\Logger
     */
    'LOGGER_INIT_DEFAULT' => true,
    'LOGGER_FILE' => $log . 'test.log',
    'LOGGER_RESET_LOG' => false,
    'LOGGER_ROOT' => 'WebDev',
    'LOGGER_LOG_TRACES' => false,

    /**
     * @see Gideon\Handler\Locale
     */
    'LOCALE_DEFAULT' => 'en_EN',
    'LOCALE_SESSION_ID' => 'sometestsesasdin',
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
     * @see RouterSpeedTest
     */
    'TEST_INT_MAX_PARAMS' => 10,
    'TEST_INT_ROUTES' => 100,
    'TEST_INT_REQUESTS' => 1000,

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
    'TEXT_REPLACE_UNDEFINED' => '??undefined',
    'TEXT_HTML_RENDER_IN_PRE' => true,

    /**
     * @see DebugTest
     */
    'TEST_LOGFILE' => $log . 'log.test',

    /**
     * @see ConfigTest
     */
    'TEST_CONFIG_LOADED' => 'tstcnfglded',

    /**
     * @see RendererTest
     */
     'TEST_RENDERER_TEXT_CONFIG' => 'Currently testing text rendering.',

    /**
     * @see ConnectionTest
     */
    'TEST_MYSQL_HOST' => '127.0.0.1',
    'TEST_MYSQL_PORT' => 3306,

    /**
     * @see LocaleTest
     */
    'TEST_NOT_DEFAULT_LOCALE' => 'pl_PL',
    'TEST_NOT_EXISTING_KEY' => 'someNotExistingKey',
    'TEST_EXISTING_KEY' => 'TEXT'
];
