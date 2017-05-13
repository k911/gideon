<?php
namespace Gideon\Application;

use Gideon\Handler\Container\FileContainer;

/**
 * Config keys used:
 * - LOCALE_SESSION_ID
 * - LOCALE_DEFAULT
 * - LOCALE_PATH
 */
class Locale extends FileContainer
{
    /**
     * Regex pattern for locale name
     * @var string
     */
    const PATTERN = '[a-z]{2}_[A-Z]{2}';

    /**
     * Name of active locale, it must pass validateLocale() validation
     * @var string
     */
    private $localeActive;

    /**
     * Base locale name, values from there are imported
     * whenever active locale doesnt have proper key
     * @var string
     */
    private $localeDefault;

    /**
     * @var string $localePath
     */
    private $localePath;

    /**
     * Name of session used to maintain active locale
     * @var string
     */
    private $localeSid;

    /**
     * Indicates whether function importUniqueDefaults() was executed
     * @var boolean
     */
    private $importedUniqueDefaults = false;

    /**
     * Indicates whether container data can be extended by default values
     * @return bool
     */
    public function isImportPossible(): bool
    {
        return !$this->importedUniqueDefaults && $this->localeActive !== $this->localeDefault;
    }

    /**
     * Check wheter given locale is valid with locale pattern
     * @param string $locale
     * @return bool
     */
    public function validateLocale(string $locale): bool
    {
        return preg_match('~^' . self::PATTERN . '$~', $locale) === 1;
    }

    /**
     * @override
     */
    protected function createPathFromName(string $locale): string
    {
        return $this->localePath . "$locale.php";
    }

    /**
     * Saves active langague to set session
     * @return self
     */
    public function setSession(): self
    {
        $_SESSION[$this->localeSid] = $this->localeActive;
    }

    /**
     * Gets value from session
     * @return string|null
     */
    public function getSession(): ?string
    {
        return $_SESSION[$this->localeSid] ?? null;
    }

    /**
     * Performs update of object and session
     * @param string $locale
     * @return self
     */
    public function setLocale(string $locale)
    {
        if ($this->localeActive !== $locale) {
            if (!$this->validateLocale($locale)) {
                $this->getLogger()->warning("Locale `$locale` is not valid. Setting default.");
                $locale = $this->localeDefault;
            }

            $this->data = $this->loadFromFile($locale);
            $this->localeActive = $locale;
        }
        return $this;
    }

    /**
     * Returns active locale
     * @return string
     */
    public function getLocale(): string
    {
        return $this->localeActive ?? $this->localeDefault;
    }

    /**
     * Performs an import form default locale file
     * to extension, without overriding already set values
     * @return this
     */
    private function importUniqueDefaults(): self
    {
        if ($this->isImportPossible()) {
            $uniques = $this->loadFromFile($this->localeDefault);
            $uniques = array_filter($uniques, function ($key) {
                return !$this->has($key);
            }, ARRAY_FILTER_USE_KEY);
            $this->extend($uniques);
            $this->importedUniqueDefaults = true;
            $this->getLogger()->info('Performend import of default locale unique values ('. count($uniques) . ')');
        }
        return $this;
    }

    /**
     * Gets value from container by given key
     * @override
     * @param string $key
     * @return mixed|null
     */
    public function findOne(string $key)
    {
        $result = parent::findOne($key);
        if (is_null($result) && $this->isImportPossible()) {
            $this->importUniqueDefaults();
            $result = parent::findOne($key);
        }
        return $result;
    }

    /**
     * Checks wheter value exists in container
     * @override
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $result = parent::has($key);
        if (is_null($result) && $this->isImportPossible()) {
            $this->importUniqueDefaults();
            $result = parent::findOne($key);
        }
        return $result;
    }

    /**
     * Alias to function findOne()
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->findOne($key);
    }

    public function __construct(Config $config, string $locale = null)
    {
        $this->localeDefault = $config->get('LOCALE_DEFAULT');
        $this->localePath = $config->get('LOCALE_PATH');
        $this->localeSid = $config->get('LOCALE_SESSION_ID');

        $this->setLocale($locale ?? $this->localeDefault);
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return array_merge(parent::getDebugProperties(),
        [
            'localeActive' => $this->localeActive,
            'localeDefault' => $this->localeDefault,
            'localePath' => $this->localePath,
            'localeSid' => $this->localeSid,
            'importedUniqueDefaults' => $this->importedUniqueDefaults,
            '$_SESSION[localeSid]' => $_SESSION[$this->localeSid]
        ]);
    }
}
