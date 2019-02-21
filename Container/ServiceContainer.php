<?php
namespace OverNick\Support\Container;

use GuzzleHttp\Client;
use OverNick\Support\Config;
use Pimple\Container;

/**
 * 组件基类
 *
 * @property Config $config
 * @property Client $client
 *
 * Class ServiceContainer
 * @package OverNick\QiYueSuo\Kernel
 */
class ServiceContainer extends Container
{
    /**
     * @var array
     */
    protected $providers = [];

    public function __construct(array $config = [])
    {
        $this->offsetSet('config', new Config($config));

        parent::__construct([]);

        if($this->config->get('client') === true){
            $this->bootstrapHttpClient();
        }

        $this->registerProviders($this->providers);
    }

    /**
     * 启动请求组件
     *
     * @param Client $client
     * @return $this
     */
    public function bootstrapHttpClient($client = null)
    {
        if($client instanceof Client){
            $this->offsetSet('client', $client);
        }else{
            $this->register(new HttpClientServiceProvider);
        }

        return $this;
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }

}