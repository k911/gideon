<?php
namespace Gideon\Handler;

use Gideon\Debug\Base as Debug;
use Gideon\Handler\Config;

/**
 * Config keys used:
 * - LOCALE_SESSION_ID
 * - LOCALE_DEFAULT
 * - LOCALE_PATH
 */
class Locale extends Debug
{
    /**
     * @var string PATTERN regex pattern of the langauge file (eg. 'en_EN')
     * @var string[] $data array consisting all the language strings (completed with default ones if not found all)
     * @var string $language is the current langauge in PATTERN style
     * @var \Gideon\Handler\Config $config external variables that this object depends on
     */
    const PATTERN = '[a-z]{2}_[A-Z]{2}';
    private $data;
    private $extended = false;
    private $language;
    private $config;

    // TODO: tests
    private function saveLanguage(string $name) 
    {
        $sid = $this->config->get('LOCALE_SESSION_ID');
        // TODO: Check if it is faster to check if value is the same then replace to value,
        //       than replace regardless to value;
        $_SESSION[$sid] = $this->language = $name;
        $this->extended = ($name == $this->config->get('LOCALE_DEFAULT')); 
    }

    // TODO: tests
    private function loadLanguage(): string 
    {
        $sid = $this->config->get('LOCALE_SESSION_ID');
        return isset($_SESSION[$sid]) ? $_SESSION[$sid] : $this->config->get('LOCALE_DEFAULT');
    }

    private function importUniqueDefault() 
    {
        $default = $this->config->get('LOCALE_DEFAULT');
        $file = $this->config->get('LOCALE_PATH') . $default . '.php';

        if(file_exists($file)) 
        {
            $origins = require $file;
            $i = 0;
            foreach($origins as $key => $value)
            {
                if(!isset($this->data[$key])) 
                {
                    ++$i;
                    $this->data[$key] = $value;
                }
            }

            $this->log("Imported $i unique values from default locale '$default'.");
            $this->extended = true;
        }
        else $this->log("Cannot import unique default values, due to not existing default locale '$default' file.");
    }

    public function setConfig(Config $config) 
    {
        $this->config = $config;
    }

    public function setLanguage(string $name) 
    {
        $pattern = '/^' . self::PATTERN . '$/';
        if(!preg_match($pattern, $name))
            throw new \Error("String: $name doesn't match Locale::PATTERN");

        $file = $this->config->get('LOCALE_PATH') . $name . '.php';
        if(!file_exists($file)) 
        {
            if($name != $this->config->get('LOCALE_DEFAULT')) 
            {
                $this->log("Language: $name doesn't exists. Setting defualt.");
                return $this->setLanguage($this->config->get('LOCALE_DEFAULT'));
            }
            else throw new \Error("Default locale '$name' is not set in config file.");
        }

        $this->data = require $file;
        $this->saveLanguage($name);
    }

    public function getLanguage() 
    {
        return $this->language;
    }

    public function get(string $key) 
    {
        if(!isset($this->data[$key])) 
        {
            if(!$this->extended) 
            {
                $this->importUniqueDefault();
                $key = $this->get($key);
            } 
            else $this->log('Locale: "' . $this->language . '" doesn\'t recognize key: "' . $key . '".');
        }
        else $key = $this->data[$key];
        return $key;
    }

    /**
     * Example usage:
     * $locale = new Locale(...);
     * echo $locale('TEXT_TEST');
     *
     * @param array $keys array of arguments $keys => [$arg1, $arg2, $arg3]
     * @return mixed null, string or string[]
     */
    public function __invoke(...$keys)
    {
        $count = count($keys);
        if($count === 1 && is_string($keys[0]))
            return $this->get($keys[0]);
        elseif ($count > 1)
        {
            $result = [];
            foreach($keys as $key)
            {
                if(is_string($key))
                {
                    $result[] = $this->get($key);
                }
            }
            return $result;
        }
    }
    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function has(string $key): bool
    {
        if(!isset($this->data[$key]))
        {
            if(!$this->extended)
            {
                $this->importUniqueDefault();
                return isset($this->data[$key]);
            }
            return false;
        }
        return true;
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __construct(Config $config, $language = null) 
    {
        $this->config = $config;
        if(!isset($language))
            $language = $this->loadLanguage();
            
        $this->setLanguage($language);
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array 
    {
        return [
            'data' => $this->data, 
            'extended' => $this->extended, 
            'langauge' => $this->language
        ];
    }
}