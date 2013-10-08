<?php
//$Id: logService.php,v 1.25 2005/12/02 23:16:09 ackbarr Exp $

require_once(XHELP_CLASS_PATH.'/xhelpService.php');

/**
 * xhelp_logService class
 *
 * Part of the Messaging Subsystem.  Uses the xhelplogMessageHandler class for logging
 *
 * @author Brian Wahoff <ackbarr@xoops.org>
 * @access public
 * @package xhelp
 */
class xhelpLogService extends xhelpService
{
    /**
     * Instance of the xhelplogMessageHandler
     *
     * @var	object
     * @access	private
     */
    var $_hLog;


    /**
     * Class Constructor
     *
     * @access	public
     */
    function xhelpLogService()
    {
        $this->_hLog =& xhelpGetHandler('logMessage');
        $this->init();
    }

    function _attachEvents()
    {
        $this->_attachEvent('batch_dept', $this);
        $this->_attachEvent('batch_owner', $this);
        $this->_attachEvent('batch_priority', $this);
        $this->_attachEvent('batch_response', $this);
        $this->_attachEvent('batch_status', $this);
        $this->_attachEvent('close_ticket', $this);
        $this->_attachEvent('delete_file', $this);
        $this->_attachEvent('edit_response', $this);
        $this->_attachEvent('edit_ticket', $this);
        $this->_attachEvent('merge_tickets', $this);
        $this->_attachEvent('new_response', $this);
        $this->_attachEvent('new_response_rating', $this);
        $this->_attachEvent('new_ticket', $this);
        $this->_attachEvent('reopen_ticket', $this);
        $this->_attachEvent('update_owner', $this);
        $this->_attachEvent('update_priority', $this);
        $this->_attachEvent('update_status', $this);
        $this->_attachEvent('new_faq', $this);

    }

    /**
     * Callback function for the 'new_ticket' event
     * @param	xhelpTicket	$ticket Ticket that was added
     * @return  bool True on success, false on error
     * @access	public
     */
    function new_ticket($ticket)
    {
        global $xoopsUser;
         
        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $ticket->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('posted'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));

        if($xoopsUser->getVar('uid') == $ticket->getVar('uid')){
            $logMessage->setVar('action', _XHELP_LOG_ADDTICKET);
        } else {
            // Will display who logged the ticket for the user
            $logMessage->setVar('action', sprintf(_XHELP_LOG_ADDTICKET_FORUSER, $xoopsUser->getUnameFromId($ticket->getVar('uid')), $xoopsUser->getVar('uname')));
        }

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'update_priority' event
     * @param	xhelpTicket	$ticket Ticket that was modified
     * @param int $oldpriority Original ticket priority
     * @return  bool True on success, false on error
     * @access	public
     */
    function update_priority($ticket, $oldpriority)
    {
        global $xoopsUser;
         
        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_PRIORITY,  $oldpriority, $ticket->getVar('priority')));
        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'update_status' event
     * @param   xhelpTicket $ticket Ticket that was modified
     * @param   xhelpStatus $oldstatus Original ticket status
     * @param   xhelpStatus $newstatus New ticket status
     * @return  bool True on success, false on error
     * @access	public
     */
    function update_status($ticket, $oldstatus, $newstatus)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_STATUS, $oldstatus->getVar('description'), $newstatus->getVar('description')));
        return $this->_hLog->insert($logMessage, true);
    }
     
    /**
     * Event: update_owner
     * Triggered after ticket ownership change (Individual)
     * Also See: batch_owner
     * @param xhelpTicket $ticket Ticket that was changed
     * @param int $oldowner UID of previous owner
     * @param int $newowner UID of new owner
     */
    function update_owner($ticket, $oldowner, $newowner)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        if ($xoopsUser->getVar('uid') == $ticket->getVar('ownership')) {
            //User claimed ownership
            $logMessage->setVar('action', _XHELP_LOG_CLAIM_OWNERSHIP);
        } else {
            //Ownership was assigned
            $logMessage->setVar('action', sprintf(_XHELP_LOG_ASSIGN_OWNERSHIP, $xoopsUser->getUnameFromId($ticket->getVar('ownership'))));
        }
        return $this->_hLog->insert($logMessage);
    }
     
     
    /**
     * Callback function for the reopen_ticket event
     * @param xhelpTicket $ticket Ticket that was re-opened
     * @return bool True on success, false on error
     * @access public
     */
    function reopen_ticket($ticket)
    {
        global $xoopsUser;
         
        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('action', _XHELP_LOG_REOPEN_TICKET);
        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the close_ticket event
     * @param xhelpTicket $ticket Ticket that was closed
     * @return bool True on success, false on error
     * @access public
     */
    function close_ticket($ticket)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated',$ticket->getVar('lastUpdated'));
        $logMessage->setVar('action', _XHELP_LOG_CLOSE_TICKET);
        return $this->_hLog->insert($logMessage);
    }

    /**
     * Add Log information for 'new_response' event
     * @param xhelpTicket $ticket Ticket for Response
     * @param xhelpResponses $newResponse Response that was added
     * @return bool True on success, false on error
     * @access public
     */
    function new_response($ticket, $newResponse)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('action', _XHELP_LOG_ADDRESPONSE);
        $logMessage->setVar('lastUpdated', $newResponse->getVar('updateTime'));
        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'new_response_rating' event
     * @param xhelpRating $rating Rating Information
     * @param xhelpTicket $ticket Ticket for Rating
     * @param xhelpResponses $response Response that was rated
     * @return bool True on success, false on error
     * @access public
     */
    function new_response_rating($rating, $ticket, $response)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $rating->getVar('ticketid'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_ADDRATING, $rating->getVar('responseid')));
        $logMessage->setVar('lastUpdated', time());
        return $this->_hLog->insert($logMessage);
    }
    /**
     * Callback function for the 'edit_ticket' event
     * @param	xhelpTicket	$oldTicket Original Ticket Information
     * @param   xhelpTicket $ticketInfo New Ticket Information
     * @return  bool True on success, false on error
     * @access	public
     */
    function edit_ticket($oldTicket, $ticketInfo)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticketInfo->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticketInfo->getVar('posted'));
        $logMessage->setVar('posted', $ticketInfo->getVar('posted'));
        $logMessage->setVar('action', _XHELP_LOG_EDITTICKET);
        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'edit_response' event
     * @param	array	$args Array of arguments passed to EventService
     * @return  bool True on success, false on error
     * @access	public
     */
    function edit_response($ticket, $response, $oldticket, $oldresponse)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $response->getVar('ticketid'));
        $logMessage->setVar('lastUpdated', $response->getVar('updateTime'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_EDIT_RESPONSE, $response->getVar('id')));
        return $this->_hLog->insert($logMessage);
    }

    /**
     * Add Log Events for 'batch_dept' event
     * @param array $tickets Array of xhelpTicket objects
     * @param xhelpDepartment $dept New department for tickets
     * @return bool True on success, false on error
     * @access public
     */
    function batch_dept($tickets, $dept)
    {
        global $xoopsUser;

        $hDept   =& xhelpGetHandler('department');
        $deptObj =& $hDept->get($dept);

        foreach($tickets as $ticket) {
            $logMessage =& $this->_hLog->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', time());
            $logMessage->setVar('action', sprintf(_XHELP_LOG_SETDEPT, $deptObj->getVar('department')));
            $this->_hLog->insert($logMessage);
            unset($logMessage);
        }
        return true;
    }

    /**
     * Add Log Events for 'batch_priority' event
     * @param array $tickets Array of xhelpTicket objects
     * @param int $priority New priority level for tickets
     * @return bool True on success, false on error
     * @access public
     */
    function batch_priority($tickets, $priority)
    {
        global $xoopsUser;

        $priority = intval($priority);
        foreach($tickets as $ticket) {
            $logMessage =& $this->_hLog->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
            $logMessage->setVar('posted', $ticket->getVar('posted'));
            $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_PRIORITY,  $ticket->getVar('priority'), $priority));
            $this->_hLog->insert($logMessage);
        }
        return true;
    }

    /**
     * Add Log Events for 'batch_owner' event
     * @param array $tickets Array of xhelpTicket objects
     * @param int $owner New owner for tickets
     * @return bool True on success, false on error
     * @access public
     */
    function batch_owner($tickets, $owner)
    {
        global $xoopsUser;

        $updated   = time();
        $ownername = ($xoopsUser->getVar('uid') == $owner ? $xoopsUser->getVar('uname') : $xoopsUser->getUnameFromId($owner));
        foreach ($tickets as $ticket) {
            $logMessage =& $this->_hLog->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $updated);
            if ($xoopsUser->getVar('uid') == $owner) {
                $logMessage->setVar('action', _XHELP_LOG_CLAIM_OWNERSHIP);
            } else {
                $logMessage->setVar('action', sprintf(_XHELP_LOG_ASSIGN_OWNERSHIP, $ownername));
            }
            $this->_hLog->insert($logMessage);
            unset($logMessage);
        }
        return true;
    }

    /**
     * Add Log Events for 'batch_status' event
     * @param array $tickets Array of xhelpTicket objects
     * @param int $newstatus New status for tickets
     * @return bool True on success, false on error
     * @access public
     */
    function batch_status($tickets, $newstatus)
    {
        global $xoopsUser;

        $updated = time();
        $sStatus = xhelpGetStatus($newstatus);
        $uid     = $xoopsUser->getVar('uid');
        foreach ($tickets as $ticket) {
            $logMessage =& $this->_hLog->create();
            $logMessage->setVar('uid', $uid);
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $updated);
            $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_STATUS, xhelpGetStatus($ticket->getVar('status')), $sStatus));
            $this->_hLog->insert($logMessage, true);
            unset($logMessage);
        }
        return true;
    }

    /**
     * Event: batch_response
     * Triggered after a batch response addition
     * Note: the $response->getVar('ticketid') field is empty for this function
     * @param array $tickets The xhelpTicket objects that were modified
     * @param xhelpResponses $response The response added to each ticket
     */
    function batch_response($tickets, $response)
    {
        global $xoopsUser;

        $updateTime = time();
        $uid        = $xoopsUser->getVar('uid');

        foreach($tickets as $ticket) {
            $logMessage =& $this->_hLog->create();
            $logMessage->setVar('uid', $uid);
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('action', _XHELP_LOG_ADDRESPONSE);
            $logMessage->setVar('lastUpdated', $updateTime);
            $this->_hLog->insert($logMessage);
        }
        return true;
    }

    /**
     * Add Log Events for 'merge_tickets' event
     * @param int $ticketid First ticket being merged
     * @param int $mergeTicketid Second ticket being merged
     * @param int $newTicket Resulting merged ticket
     * @return bool True on success, false on error
     * @access public
     */
    function merge_tickets($ticketid, $mergeTicketid, $newTicket)
    {
        global $xoopsUser;


        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticketid);
        $logMessage->setVar('action', sprintf(_XHELP_LOG_MERGETICKETS, $mergeTicketid, $ticketid));
        $logMessage->setVar('lastUpdated', time());
        if($this->_hLog->insert($logMessage)){
            return true;
        }
        return false;
    }

    /**
     * Add Log Events for 'delete_file' event
     * @param xhelpFile $file File being deleted
     * @return bool True on success, false on error
     * @access public
     */
    function delete_file($file)
    {
        global $xoopsUser;

        $filename = $file->getVar('filename');

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $file->getVar('ticketid'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_DELETEFILE, $filename));
        $logMessage->setVar('lastUpdated', time());

        if($this->_hLog->insert($logMessage, true)){
            return true;
        }
        return false;
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param xhelpTicket $ticket Ticket used as base for FAQ
     * @param xhelpFaq $faq FAQ that was added
     */
    function new_faq($ticket, $faq)
    {
        global $xoopsUser;

        $logMessage =& $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_NEWFAQ, $faq->getVar('subject')));

        return $this->_hLog->insert($logMessage, true);
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
            $c = __CLASS__;
            $instance = new $c;
        }
        return($instance);
    }
}
?>