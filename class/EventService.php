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
 * EventService class
 *
 * Messaging Subsystem.  Notifies objects when events occur in the system
 *
 * <code>
 * $eventService = new xhelp_eventService();
 * // Call $obj->callback($args) when a new ticket is created
 * $eventService->advise('new_ticket', $obj, 'callback');
 * // Call $obj2->somefunction($args) when a new ticket is created
 * $eventService->advise('new_ticket', $obj2, 'somefunction');
 * // .. Code to create new ticket
 * $eventService->trigger('new_ticket', $new_ticketobj);
 * </code>
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class EventService
{
    /**
     * Array of all function callbacks
     *
     * @var array
     */
    public $_ctx = [];

    /**
     * Class Constructor
     */
    public function __construct()
    {
        //Do Nothing
    }

    /**
     * Add a new class function to be notified
     * @param string $context  Event used for callback
     * @param string $callback Function to call when event is fired. If only object is supplied, look for function with same name as context
     * @param int    $priority Order that callback should be triggered
     * @return int      Event cookie, used for unadvise
     */
    public function advise(string $context, string $callback, int $priority = 10): int
    {
        $clbk = $callback;
        if (!\is_array($callback) && \is_object($callback)) {
            $clbk = [$callback, $context];
        }

        //Add Element to notification list
        $this->_ctx[$context][(string)$priority][] = $clbk;

        //Return element # in array
        return \count($this->_ctx[$context][(string)$priority]) - 1;
    }

    /**
     * Remove a class function from the notification list
     * @param string $context Event used for callback
     * @param int    $cookie  The Event ID returned by xhelp_eventService::advise()
     * @param int    $priority
     */
    public function unadvise(string $context, int $cookie, int $priority = 10): void
    {
        $this->_ctx[$context][(string)$priority][$cookie] = false;
    }

    /**
     * Only have 1 instance of class used
     * @return EventService {@link EventService}
     */
    public static function getInstance(): EventService
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Tell subscribed objects an event occurred in the system
     * @param string $context Event raised by the system
     * @param array  $args    Any arguments that need to be passed to the callback functions
     */
    public function trigger(string $context, array $args): void
    {
        if (isset($this->_ctx[$context])) {
            \ksort($this->_ctx[$context]);
            $_notlist = $this->_ctx[$context];
            foreach ($_notlist as $priority => $functions) {
                foreach ($functions as $func) {
                    if (\is_callable($func, true, $func_name)) {
                        \call_user_func_array($func, $args);
                    }
                }
            }
        }
    }
}
