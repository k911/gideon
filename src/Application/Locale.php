<?php
namespace Gideon\Application;

use Error;
use Gideon\Debug\Provider as Debug;
use Gideon\Application\Config;
use Gideon\Exception\IOException;

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
     */
    const PATTERN = '[a-z]{2}_[A-Z]{2}';
    private $data;
    private $extended = false;
    private $language;

    /**
     * @var string $localeDefault default langauge name e.g. 'en_EN'
     */
    private $localeDefault;

    /**
     * @var string $localePath
     */
    private $localePath;

    /**
     * @var string $sid
     */
    private $sid;

    private function saveLanguage(string $name)
    {
        $_SESSION[$this->sid] = $this->language = $name;
    }

    private function loadLanguage(): string
    {
        return $_SESSION[$this->sid] ?? $this->localeDefault;
    }

    private function importUniqueDefault()
    {
        $file = $this->localePath . $this->localeDefault. '.php';

        if(!file_exists($file))
        {
            throw new IOException("Can't find default locale `{$this->localeDefault}` file.", $file);
        }

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

        $this->getLogger()->debug("Imported $i unique values from default locale `{$this->localeDefault}`.");
        $this->extended = true;
    }

    public function setLanguage(string $name)
    {
        $pattern = '/^' . self::PATTERN . '$/';
        if(!preg_match($pattern, $name))
            throw new Error("String: $name doesn't match Locale::PATTERN");

        $file = $this->localePath. $name . '.php';
        if(!file_exists($file))
        {
            if($name != $this->localeDefault)
            {
                $this->getLogger()->warning("Language: $name doesn't exists. Setting defualt.");
                return $this->setLanguage($this->localeDefault);
            }
            else throw new Error("Default locale '$name' is not set in config file.");
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
            else $this->getLogger()->warning('Locale: "' . $this->language . '" doesn\'t recognize key: "' . $key . '".');
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

    public function __construct(Config $config, string $language = null)
    {
        $this->localeDefault = $config->get('LOCALE_DEFAULT');
        $this->localePath = $config->get('LOCALE_PATH');
        $this->sid = $config->get('LOCALE_SESSION_ID');

        if(is_null($language))
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
