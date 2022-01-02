<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;


/**
 * Plugin Interface
 *
 * Defines the interface for xhelp plugins
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @todo    Localization of meta information
 */
class Plugin implements PluginInterface
{
    /**
     * Array of Plugin Meta Information
     * @var array
     */
    public $_meta = [];
    /**
     * Array of subscribed events
     * @var array
     */
    public $_events = [];
    /**
     * A reference to a {@link EventService} object
     * @var EventService
     */
    public $_event_srv;

    /**
     * Class Constructor
     * @param EventService $event_srv a reference to a {@link EventService} object
     */
    public function __construct($event_srv)
    {
        $this->_event_srv = $event_srv;
    }

    /**
     * Retrieve the specified meta field
     * @param string $var name of variable to return
     * @return string if var is set, false if not
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
     */
    public function onLoad()
    {
        //Initialize any event handlers
        $this->registerEventHandler('new_event', 'on_new_event');
    }

    /**
     * Destruction function, triggered when a plugin is "un-loaded" by the system
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
     * @return Plugin {@link Plugin}
     */
    public static function getInstance(): Plugin
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
