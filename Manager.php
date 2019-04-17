<?php
namespace OverNick\Support;

use ArrayAccess;
use Closure;
use InvalidArgumentException;

/**
 * Class Manager
 * @package OverNick\Support
 */
abstract class Manager implements ArrayAccess
{
    /**
     * The application instance.
     */
    protected $app;

    /**
     * @var array
     */
    protected $configure;

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * @var string
     */
    protected $driver;

    /**
     * Create a new manager instance.
     *
     * @param  $app
     * @param array $config
     * @param string $driver
     */
    public function __construct(array $config = [], $app = null, $driver = null)
    {
        $this->configure = $config;
        $this->app = $app;
        $this->driver = $driver;
    }

    /**
     * 获取配置文件
     *
     * @param null $key
     * @param null $default
     * @return mixed
     */
    public function getConfigure($key = null, $default = null)
    {
        return Arr::get($this->configure, $key, $default);
    }

    /**
     * 设置配置文件
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function setConfigure($key, $value)
    {
        Arr::set($this->configure, $key, $value);
        return $this;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->driver ?? $this->getConfigure('default');
    }

    /**
     * @param $driver
     * @return $this
     */
    public function setDefaultDriver($driver = null)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return $this
     */
    public function driver($driver = null)
    {
        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create a new driver instance.
     *
     * @param  string  $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } else {
            $method = 'create'.Str::studly($driver).'Driver';

            if (method_exists($this, $method)) {
                return $this->$method();
            }
        }
        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * Call a custom driver creator.
     *
     * @param  string  $driver
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->customCreators[$driver]($this->app);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created "drivers".
     *
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver($this->getDefaultDriver())->$method(...$parameters);
    }

    /**
     * call the default driver instance.
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->driver($this->getDefaultDriver())->$name;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->extend($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->getDrivers());
    }

    /**
     * @param mixed $offset
     * @return mixed|Manager
     */
    public function offsetGet($offset)
    {
        return $this->driver($offset);
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

}
