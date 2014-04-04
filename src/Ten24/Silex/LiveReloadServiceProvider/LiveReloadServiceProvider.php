<?php
/*
 * This file is part of LiveReloadServiceProvider. 
 * 
 * Thanks to Tobiass Josten for inspiration for his [unknown] mentorship
 * @url https://github.com/tobiassjosten/ResponsibleServiceProvider
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
     * 
     * Set the default options & replace with applicaiton-defined options
     * 
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
        
        if($app->offsetExists('ten24.livereload.options'))
        {
            $app['ten24.livereload.options'] = $app->share(function() {
                array_merge(
                    $app['ten24.livereload.options'],
                    $defaults);
            });
        }
    }

    /**
     * Bootstraps the application.
     * 
     * Adds the LiveReloadListener
     * 
     * (non-PHPdoc)
     * @see \Silex\ServiceProviderInterface::boot()
     */
    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(
            new LiveReloadListener(), 
            $app['ten24.livereload.options']);
    }
}