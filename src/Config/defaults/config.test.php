<?php

// Root directory for application
$root = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR;

// Src directory is root directory of source code (used only for defaults configs/locales)
$src = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;
$locale = $src . 'Locale' . DIRECTORY_SEPARATOR . 'defaults' . DIRECTORY_SEPARATOR;

// Application directory in public_html folder.
// http(s)://domain.com/{$alias}
// e.g. for `localhost/gideon` => 'gideon/'
// and for `localhost` => '' (no slash if in root public directory)
$alias = 'gideon/';

// Useful pathes
$public = $root . 'public' . DIRECTORY_SEPARATOR;
$view = $root . 'view' . DIRECTORY_SEPARATOR;
$log = $root . 'var/log' . DIRECTORY_SEPARATOR;

return
[
    /**
     * Basic informations, paths, urls
     */
    'ALIAS' => $alias,

    /**
     * @see Gideon\Application
     */
    'APPLICATION_CONTROLLER_PREFIX' => 'Gideon\\Controller\\',
    'APPLICATION_CLOSURE_ACTION_NAME' => 'anonymous',

    /**
     * @see Gideon\Cache\SimpleCache
     */
    'CACHE_PATH' => $root . 'var/tests/cache',
    'CACHE_MODE_DIR' => '0700',
    'CACHE_MODE_FILE' => '0600',
    'CACHE_TTL_DEFAULT' => 1000,
    'CACHE_HASH_DEFAULT' => 'sha256',

    /**
     * @see Gideon\Database\Connection\MySQL
     */
    'MYSQL_SETTINGS_DEFAULT' =>
    [
        'host' => 'localhost',
        'dbname' => 'gideon',
        'username' => 'gideon-debug',
        'password' => '',
        'charset' => 'utf8'
    ],

    /**
     * @see Gideon\Debug\Logger
     */
    'LOGGER_INIT_DEFAULT' => true,
    'LOGGER_DIR' => $log,
    'LOGGER_FILE' => 'test.log',
    'LOGGER_RESET_LOG' => true,
    'LOGGER_ROOT' => 'gideon',
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
     * @see Gideon\Http\Response\Base
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

    /**
     * @see Gideon\Http\Response\View
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
    'TEXT_REPLACE_UNDEFINED' => '??undefined',
    'TEXT_HTML_RENDER_IN_PRE' => true,

    /**
     * @see DebugTest
     */
    'TEST_LOGFILE' => 'log.test',

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
