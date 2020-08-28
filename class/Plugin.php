<?php

namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

/**
 * Plugin Interface
 *
 * Defines the interface for xhelp plugins
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 * @todo    Localization of meta information
 */
class Plugin
{
    /**
     * Array of Plugin Meta Information
     * @access private
     * @var array
     */
    public $_meta = [];

    /**
     * Array of subscribed events
     * @var array
     * @access private
     */
    public $_events = [];

    /**
     * A reference to a {@link Xhelp\EventService} object
     * @var EventService
     * @access private
     */
    public $_event_srv;

    /**
     * Class Constructor
     * @param EventService $event_srv a reference to a {@link Xhelp\EventService} object
     */
    public function __construct($event_srv)
    {
        $this->_event_srv = $event_srv;
    }

    /**
     * Retrieve the specified meta field
     * @param string $var name of variable to return
     * @return string if var is set, false if not
     * @access public
     */
    public function getMeta($var)
    {
        return ($this->_meta[$var] ?? false);
    }

    /**
     * @param $var
     * @param $value
     */
    public function setMeta($var, $value)
    {
        $this->_meta[$var] = $value;
    }

    /**
     * Initialization function, triggered when a plugin is "loaded" by the system
     * @access public
     */
    public function onLoad()
    {
        //Initialize any event handlers
        $this->registerEventHandler('new_event', 'on_new_event');
    }

    /**
     * Destruction function, triggered when a plugin is "un-loaded" by the system
     * @access public
     */
    public function onUnload()
    {
        //Remove any registered events
        foreach ($this->_events as $event_ctx => $event_cookies) {
            foreach ($event_cookies as $cookie) {
                $this->_event_srv->unadvise($event_ctx, $cookie);
            }
        }
    }

    /**
     * Add a function to be called when an event is triggered by the system
     * @access protected
     * @param $event_ctx
     * @param $event_func
     */
    public function registerEventHandler($event_ctx, $event_func)
    {
        if (!isset($this->_events[$event_ctx])) {
            $this->_events[$event_ctx] = [];
        }

        $this->_events[$event_ctx][] = $this->_event_srv->advise($event_ctx, $this, $event_func);
    }

    /**
     * Only have 1 instance of class used
     * @return Plugin {@link Xhelp\Plugin}
     * @access  public
     */
    public static function getInstance()
    {
        // Declare a static variable to hold the object instance
        static $instance;

        // If the instance is not there, create one
        if (null === $instance) {
            //            $instance = new $this->getMeta('classname');
            $instance = new static();
        }

        return $instance;
    }
}
