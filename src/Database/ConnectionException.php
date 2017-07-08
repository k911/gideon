<?php
declare(strict_types=1);

namespace Gideon\Database;

use Gideon\Exception\RuntimeException;

class ConnectionException extends RuntimeException
{
    /**
     * Connection credentials
     * @var array
     */
    private $credentials;

    public function getCredentials(): array
    {
        return $this->credentials;
    }

    public function setCredentials(string $username, string $password = null, string $dsn = null, array $options = null): self
    {
        $this->credentials['username'] = $username;
        if (isset($password)) {
            $this->credentials['password'] = $password;
        }
        if (isset($dsn)) {
            $this->credentials['dsn'] = $dsn;
        }
        if (isset($options)) {
            $this->credentials['options'] = $options;
        }
        return $this;
    }

    public function getGetters(): array
    {
        return array_merge(parent::getGetters(), [
            'credentials',
        ]);
    }
}
