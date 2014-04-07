<?php
/*
 * This file is part of live-reload-service-provider. 
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 * 
 * @author blair <blair@tentwentyfour.ca>
 */
namespace Ten24\Silex\Provider;

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
        $app['ten24.livereload.default_options'] = array(
                'host' => 'localhost',
                'port' => 35729,
                'enabled' => true,
                'check_server_presence' => true);
        
        $app['ten24.livereloader'] = $app->share(function($app){
            
            if ($app->offsetExists('ten24.livereload.options'))
            {
                $app['ten24.livereload.options'] = array_merge($app['ten24.livereload.default_options'], $app['ten24.livereload.options']);
            }
            else 
            {
                $app['ten24.livereload.options'] = $app['ten24.livereload.default_options'];
            }
            
            $params = $app['ten24.livereload.options'];
            
        	return new LiveReloadListener($params);
        });
    }

    /**
     * Add the listener
     * (non-PHPdoc)
     * @see \Silex\ServiceProviderInterface::boot()
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(
            $app['ten24.livereloader']);
    }
}