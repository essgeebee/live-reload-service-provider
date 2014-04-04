<?php

/*
 * This file is part of LiveReloadServiceProvider.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @author blair <blair@tentwentyfour.ca>
 */

use Silex\Application;
use Silex\WebTestCase;
use Silex\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Ten24\Silex\LiveReloadServiceProvider\LiveReloadServiceProvider;

class LiveReloadTest extends WebTestCase
{
    /**
     * Request url
     * @var string
     */
    protected static $url = '/livereloadtest';
    
    /**
     * 
     * @return string|boolean
     */
    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();
        
        $app->register(new LiveReloadServiceProvider());
        
        $app->get(self::$url, function(){
        	return '<!doctype html><html><head><title>LiveReloadTest</title></head><body></body></html>';
        });
        
        $app['profiler'] = $app->share(function($app){
            return new Profile(substr(hash('sha256', uniqid(mt_rand(), true)), 0, 6));
        });
        
        $app->after(function(Request $request, Response $response) use ($app) {
            $response->headers->set('X-Debug-Token', $app['profiler']->getToken());
        });
        
        return $app;
    }
    
    /**
     * Check response for livereload script tag 
     * with the application's configuration
     */
    public function testScriptInjection()
    {   
        $client = $this->createClient();
        $crawler = $client->request('GET', self::$url);
        
        $options = $this->app['ten24.livereload.options'];
        $host = $options['host'];
        $port = $options['port'];
        
        $scriptSrc = 'http://'.$host.':'.$port.'/livereload.js';
        $response = $client->getResponse();
           
        $this->assertTrue($response->isOk());
        $this->assertCount(1, $crawler->filter('script[src="'.$scriptSrc.'"]'));
    }

    /**
     * Simulate disabled state
     */
    public function testEnabledOption()
    {
        $this->app['ten24.livereload.options']['enabled'] = false;
        
        $client = $this->createClient();
        $crawler = $client->request('GET', self::$url);
        $response = $client->getResponse();
        
        $this->assertTrue($response->isOk());
        $this->assertCount(0, $crawler->filter('script'));
    }
    
    /**
     * Change host and port options
     */
    public function testAlternateHostAndPortOptions()
    {
        $this->app['ten24.livereload.options']['host'] = 'www.nowhere.com';
        $this->app['ten24.livereload.options']['port'] = '3179';
        
        // Won't check that livereload is actually running there, that's out of scope.
        $this->app['ten24.livereload.options']['check_server_presence'] = false;
        
        $host = $this->app['ten24.livereload.options']['host'];
        $port = $this->app['ten24.livereload.options']['port'];
        
        $client = $this->createClient();
        $crawler = $client->request('GET', self::$url);
        $response = $client->getResponse();
        
        $scriptSrc = 'http://'.$host.':'.$port.'/livereload.js';
        
        $this->assertTrue($response->isOk());
        $this->assertCount(1, $crawler->filter('script[src="'.$scriptSrc.'"]'));
    }
}
