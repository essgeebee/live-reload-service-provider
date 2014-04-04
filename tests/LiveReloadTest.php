<?php

/*
 * This file is part of LiveReloadServiceProvider.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @author blair <blair@tentwentyfour.ca>
 * @todo
 */

use Silex\Application;
use Silex\WebTestCase;
use Tobiassjosten\Silex\ResponsibleServiceProvider;

class LiveReloadTest extends WebTestCase
{
    public function createApplication()
    {
        $app = new Application();
        $app->register(new LiveReloadServiceProvider());
        $app['debug'] = true;
        $app['exception_handler']->disable();

        $app->get('/livereloadtest', function(){
        	return '<!doctype html><html><head><title>LiveReloadTest</title></head><body></body></html>';
        });
        
        return $app;
    }

    /**
     * Check response for livereload script with the proper
     * host/port
     */
    public function testResponse()
    {
        $client = $this->createClient();
        $client->request('GET', '/foo', array(), array(), array(
            'HTTP_ACCEPT' => 'application/json',
        ));

        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
        $this->assertEquals('["bar"]', $response->getContent());
    }

    /**
     * Simulate non-default options
     */
    public function testSetOptions()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/foo', array(), array(), array(
            'HTTP_ACCEPT' => 'text/xml',
        ));

        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<response><item key=\"0\">bar</item></response>\n",
            $response->getContent()
        );
    }

    /**
     * Simulate disabled state
     */
    public function testDisabled()
    {
        $client = $this->createClient();
        $client->request('GET', '/foo', array(), array(), array(
            'HTTP_ACCEPT' => '',
        ));

        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
        $this->assertEquals('["bar"]', $response->getContent());
    }

    private function setLiveReloadOption($key, $value)
    {
        $app['ten24.livereload.options'][$key] = $value;
    }
}
