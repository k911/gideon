<?php
namespace Gideon\Database\Connection;

use PDO;
use PDOException;
use Gideon\Debug\Provider as Debug;
use Gideon\Config;
use Gideon\Database\Connection;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Handler\Call\SafeCall;

abstract class Base extends Debug implements Connection
{
    /**
     * @var \PDO    $PDO PHP Data Object
     */
    private $PDO;

    /**
     * @var string  $DSN Data Source Names
     */
    private $DSN;

    /**
     * @var string  $username db auth
     */
    private $username;

    /**
     * @var string  $password for provided username
     */
    private $password;

    /**
     * @var array $options 4th argument of \PDO constructor
     * @link http://php.net/manual/en/class.pdo.php
     */
    private $options;

    public function __call(string $function, array $arguments)
    {
        if (isset($this->PDO)) {
            if (method_exists($this->PDO, $function)) {
                return call_user_func_array([$this->PDO, $function], $arguments);
            } else {
                throw new InvalidArgumentException("Function: PDO->$function() doesn't exists.");
            }
        } else {
            throw new InvalidArgumentException("Tried to call function: $function on not initialized PDO.");
        }
    }

    public function close()
    {
        unset($this->PDO);
    }

    public function connect(): Connection
    {
        if (!isset($this->PDO)) {
            $this->PDO = new PDO($this->DSN, $this->username, $this->password, $this->options);
        }

        return $this;
    }

    public function try_connect(ErrorHandler $handler): bool
    {
        if (!isset($this->PDO)) {
            $this->PDO = (new SafeCall($handler,
                function ($DSN, $username, $password, $options) {
                    return new PDO($DSN, $username, $password, $options);
                }))
                ->setArguments($this->DSN, $this->username, $this->password, $this->options)
                ->call();
            return $handler->isEmpty();
        }
        return true;
    }

    /**
     * Computes DSN from settings
     * @param array $settings
     * @return string PDO's DSN string
     */
    private function computeDSN(array $settings): string
    {
        $dsn = $settings['prefix'] . ':';
        unset($settings['prefix']);

        if (!empty($settings)) {
            foreach ($settings as $name => $v) {
                $dsn .= "$name=$v;";
            }
            $dsn = mb_substr($dsn, 0, -1);
        }
        return $dsn;
    }

    /**
     * Concatenate provided $options with default settings from $config
     * @param \Gideon\Config $config
     * @param array $options
     * @return array
     */
    abstract protected function parseSettings(Config $config, array $options = null): array;

    /**
     * Initialize PDO settings and Database credentials
     * @param \Gideon\Config $config
     * @param array                 $settings
     */
    public function __construct(Config $config, array $options = null)
    {
        $settings = $this->parseSettings($config, $options);

        // Unpack unnecessary credentials
        if (isset($settings['username'])) {
            $this->username = $settings['username'];
            unset($settings['username']);

            if (isset($settings['password'])) {
                $this->password = $settings['password'];
            }
        }
        unset($settings['password']);

        if (isset($settings['options'])) {
            $this->username = $settings['options'];
            unset($settings['options']);
        }

        // Save coputed DSN
        $this->DSN = $this->computeDSN($settings);
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        $result = [
            'pdo_driver' => isset($this->PDO) ? $this->PDO->getAttribute(PDO::ATTR_DRIVER_NAME) : 'not initialized',
            'dsn' => $this->DSN,
        ];

        if (isset($this->username)) {
            $result['username'] = $this->username;
            $result['use_password'] = isset($this->password) ? 'yes' : 'no';
        }

        return $result;
    }
}
