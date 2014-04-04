<?php
/*
 * This file is part of LiveReloadServiceProvider. 
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 * 
 * @author blair <blair@tentwentyfour.ca>
 */
namespace Ten24\Silex\LiveReloadServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class LiveReloadServiceProvider implements ServiceProviderInterface
{
    /**
     * Register this service
     * (non-PHPdoc)
     * @see \Silex\ServiceProviderInterface::register()
     */
    public function register(Application $app)
    {
        $defaults = array(
                'host' => 'localhost',
                'port' => 35729,
                'enabled' => true,
                'check_server_presence' => true);
    
        if (!$app->offsetExists('ten24.livereload.options'))
        {
            $app['ten24.livereload.options'] = new \Pimple($defaults);
        }
        
        foreach ($defaults as $key => $value)
        {
            if (!isset($app['ten24.livereload.options'][$key]))
            {
                $app['ten24.livereload.options']->set($key, $value);
            }
        }
    }

    /**
     * Add the listener
     * (non-PHPdoc)
     * @see \Silex\ServiceProviderInterface::boot()
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(
            new LiveReloadListener($app['ten24.livereload.options']));
    }
}