<?php
/**
 * Created by PhpStorm.
 * User: overnic
 * Date: 2019/2/21
 * Time: 13:58
 */
namespace OverNick\Support\Container;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * 请求组件的加载
 *
 * Class ClientServiceProvider
 * @package OverNick\QiYueSuo\Providers
 */
class HttpClientServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['client'] = function(){
            return new Client();
        };
    }

}