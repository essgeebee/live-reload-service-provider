<?php

/*
 * This file is part of LiveReloadServiceProvider.
 * 
 * Many thanks to Kunstmaan and their LiveReloadBundle for SF2 for this code
 * @url https://github.com/Kunstmaan/KunstmaanLiveReloadBundle/
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @author blair <blair@tentwentyfour.ca>
 */

namespace Ten24\Silex\LiveReloadServiceProvider;
 
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LiveReloadListener implements EventSubscriberInterface {

    /**
     * Enable the livereload server
     * @var boolean
     */
    protected $enabled;
    
    /**
     * Hostname of the livereload server
     * @var string
     */
    protected $host;
    
    /**
     * Port of the livereload server
     * @var integer
     */
    protected $port;
    
    /**
     * Check for server presence
     * @var boolean
     */
    protected $check_server_presence;

    /**
     * Constructor
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->host = $params['host'];
        $this->port = $params['port'];
        $this->enabled = $params['enabled'];
        $this->check_server_presence = $params['check_server_presence'];
    }

    /**
     * Watch Kernel Response Event
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if (!$this->enabled
            || !$response->headers->has('X-Debug-Token')
            || $response->isRedirection()
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
        ) {
            return;
        }

        $this->injectScript($response);
    }

    /**
     * Injects the livereload script.
     *
     * @param Response $response A Response instance
     */
    protected function injectScript(Response $response)
    {
        if (function_exists('mb_stripos')) 
        {
            $posrFunction   = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } 
        else 
        {
            $posrFunction   = 'strripos';
            $substrFunction = 'substr';
        }

        $content = $response->getContent();
        $pos = $posrFunction($content, '</body>');

        if (false !== $pos) 
        {
            $script = "http://$this->host:$this->port/livereload.js";

            if ($this->check_server_presence) 
            {
                $headers = @get_headers($script);
                if (!is_array($headers) || strpos($headers[0], '200') === false) {
                    return;
                }
            }

            $content = $substrFunction($content, 0, $pos)."\n<script src=\"$script\"></script>\n".$substrFunction($content, $pos);
            $response->setContent($content);
        }
    }

    /**
     * 
     * @return multitype:multitype:string number
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', -127),
        );
    }

}