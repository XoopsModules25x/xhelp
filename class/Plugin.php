<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       XOOPS Development Team
 */

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
    public $eventService;

    /**
     * Class Constructor
     * @param EventService $eventService a reference to a {@link EventService} object
     */
    public function __construct(EventService $eventService = null)
    {
        if (null !== $eventService) {
            $this->eventService = $eventService;
        }
    }

    /**
     * Retrieve the specified meta field
     * @param string $var name of variable to return
     * @return string if var is set, false if not
     */
    public function getMeta(string $var)
    {
        return ($this->_meta[$var] ?? false);
    }

    /**
     * @param string $var
     * @param string $value
     */
    public function setMeta(string $var, string $value)
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
                $this->eventService->unadvise($event_ctx, $cookie);
            }
        }
    }

    /**
     * Add a function to be called when an event is triggered by the system
     * @param string $event_ctx
     * @param string $event_func
     */
    public function registerEventHandler(string $event_ctx, string $event_func)
    {
        if (!isset($this->_events[$event_ctx])) {
            $this->_events[$event_ctx] = [];
        }

        $this->_events[$event_ctx][] = $this->eventService->advise($event_ctx, $event_func);
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
