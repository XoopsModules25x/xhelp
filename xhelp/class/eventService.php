<?php
//$Id: eventService.php,v 1.9 2005/12/01 22:38:55 ackbarr Exp $

/**
 * xhelp_eventService class
 *
 * Messaging Subsystem.  Notifies objects when events occur in the system
 *
 * <code>
 * $_eventsrv = new xhelp_eventService();
 * // Call $obj->callback($args) when a new ticket is created
 * $_eventsrv->advise('new_ticket', &$obj, 'callback');
 * // Call $obj2->somefunction($args) when a new ticket is created
 * $_eventsrv->advise('new_ticket', &$obj2, 'somefunction');
 * // .. Code to create new ticket
 * $_eventsrv->trigger('new_ticket', $new_ticketobj);
 * </code>
 *
 * @author Brian Wahoff <ackbarr@xoops.org>
 * @access public
 * @package xhelp
 */

class xhelpEventService
{
    /**
     * Array of all function callbacks
     *
     * @var	array
     * @access	private
     */
    var $_ctx = array();

    /**
     * Class Constructor
     *
     * @access	public
     */
    function xhelpEventService()
    {
        //Do Nothing
    }

    /**
     * Add a new class function to be notified
     * @param	string	$context Event used for callback
     * @param	callback $callback Function to call when event is fired. If only object is supplied, look for function with same name as context
     * @param   int $priority Order that callback should be triggered
     * @return  int Event cookie, used for unadvise
     * @access	public
     */
    function advise($context, &$callback, $priority = 10)
    {
        if (!is_array($callback) && is_object($callback)) {
            $clbk = array($callback, $context);
        } else {
            $clbk = $callback;
        }

        //Add Element to notification list
        $this->_ctx[$context]["$priority"][] = $clbk;
        //Return element # in array
        return count($this->_ctx[$context]["$priority"]) - 1;
    }


    /**
     * Remove a class function from the notification list
     * @param	string	$context Event used for callback
     * @param	int	$cookie The Event ID returned by xhelp_eventService::advise()
     * @access	public
     */
    function unadvise($context, $cookie, $priority = 10)
    {
        $this->_ctx[$context]["$priority"][$cookie] = false;
    }

    /**
     * Only have 1 instance of class used
     * @return object {@link xhelp_eventService}
     * @access	public
     */
    function &singleton()
    {
        // Declare a static variable to hold the object instance
        static $instance;

        // If the instance is not there, create one
        if(!isset($instance)) {
            $instance = new xhelpEventService();
        }
        return($instance);
    }

    /**
     * Tell subscribed objects an event occurred in the system
     * @param	string	$context Event raised by the system
     * @param	array	$args Any arguments that need to be passed to the callback functions
     * @access	public
     */
    function trigger($context, $args)
    {
        if (isset($this->_ctx[$context])) {
            ksort( $this->_ctx[$context] );
            $_notlist = $this->_ctx[$context];
            foreach ($_notlist as $priority => $functions) {
                foreach ($functions as $func) {
                    if (is_callable($func, true, $func_name)) {
                        call_user_func_array($func, $args);
                    }
                }
            }
        }
    }
}
?>