<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

// require_once XHELP_CLASS_PATH . '/Service.php';

/**
 * xhelp_logService class
 *
 * Part of the Messaging Subsystem.  Uses the Xhelp\LogMessageHandler class for logging
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class LogService extends Xhelp\Service
{
    /**
     * Instance of the Xhelp\LogMessageHandler
     *
     * @var object
     * @access  private
     */
    public $_hLog;

    /**
     * Class Constructor
     *
     * @access  public
     */
    public function __construct()
    {
        $this->_hLog = new Xhelp\LogMessage();
        $this->init();
    }

    public function _attachEvents()
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
     * @param  Xhelp\Ticket $ticket Ticket that was added
     * @return bool        True on success, false on error
     * @access  public
     */
    public function new_ticket($ticket)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $ticket->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('posted'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));

        if ($xoopsUser->getVar('uid') == $ticket->getVar('uid')) {
            $logMessage->setVar('action', _XHELP_LOG_ADDTICKET);
        } else {
            // Will display who logged the ticket for the user
            $logMessage->setVar('action', sprintf(_XHELP_LOG_ADDTICKET_FORUSER, $xoopsUser::getUnameFromId($ticket->getVar('uid')), $xoopsUser->getVar('uname')));
        }

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'update_priority' event
     * @param  Xhelp\Ticket $ticket      Ticket that was modified
     * @param  int         $oldpriority Original ticket priority
     * @return bool        True on success, false on error
     * @access  public
     */
    public function update_priority($ticket, $oldpriority)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_PRIORITY, $oldpriority, $ticket->getVar('priority')));

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'update_status' event
     * @param  Xhelp\Ticket $ticket    Ticket that was modified
     * @param  Xhelp\Status $oldstatus Original ticket status
     * @param  Xhelp\Status $newstatus New ticket status
     * @return bool        True on success, false on error
     * @access  public
     */
    public function update_status($ticket, $oldstatus, $newstatus)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
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
     * @param Xhelp\Ticket $ticket   Ticket that was changed
     * @param int          $oldowner UID of previous owner
     * @param int          $newowner UID of new owner
     * @return
     */
    public function update_owner($ticket, $oldowner, $newowner)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        if ($xoopsUser->getVar('uid') == $ticket->getVar('ownership')) {
            //User claimed ownership
            $logMessage->setVar('action', _XHELP_LOG_CLAIM_OWNERSHIP);
        } else {
            //Ownership was assigned
            $logMessage->setVar('action', sprintf(_XHELP_LOG_ASSIGN_OWNERSHIP, $xoopsUser::getUnameFromId($ticket->getVar('ownership'))));
        }

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the reopen_ticket event
     * @param  Xhelp\Ticket $ticket Ticket that was re-opened
     * @return bool        True on success, false on error
     * @access public
     */
    public function reopen_ticket($ticket)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('action', _XHELP_LOG_REOPEN_TICKET);

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the close_ticket event
     * @param  Xhelp\Ticket $ticket Ticket that was closed
     * @return bool        True on success, false on error
     * @access public
     */
    public function close_ticket($ticket)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('action', _XHELP_LOG_CLOSE_TICKET);

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Add Log information for 'new_response' event
     * @param  Xhelp\Ticket    $ticket      Ticket for Response
     * @param  Xhelp\Responses $newResponse Response that was added
     * @return bool           True on success, false on error
     * @access public
     */
    public function new_response($ticket, $newResponse)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('action', _XHELP_LOG_ADDRESPONSE);
        $logMessage->setVar('lastUpdated', $newResponse->getVar('updateTime'));

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'new_response_rating' event
     * @param  Xhelp\Rating    $rating   Rating Information
     * @param  Xhelp\Ticket    $ticket   Ticket for Rating
     * @param  Xhelp\Responses $response Response that was rated
     * @return bool           True on success, false on error
     * @access public
     */
    public function new_response_rating($rating, $ticket, $response)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $rating->getVar('ticketid'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_ADDRATING, $rating->getVar('responseid')));
        $logMessage->setVar('lastUpdated', time());

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'edit_ticket' event
     * @param  Xhelp\Ticket $oldTicket  Original Ticket Information
     * @param  Xhelp\Ticket $ticketInfo New Ticket Information
     * @return bool        True on success, false on error
     * @access  public
     */
    public function edit_ticket($oldTicket, $ticketInfo)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticketInfo->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticketInfo->getVar('posted'));
        $logMessage->setVar('posted', $ticketInfo->getVar('posted'));
        $logMessage->setVar('action', _XHELP_LOG_EDITTICKET);

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Callback function for the 'edit_response' event
     * @param $ticket
     * @param $response
     * @param $oldticket
     * @param $oldresponse
     * @return bool True on success, false on error
     * @internal param array $args Array of arguments passed to EventService
     * @access   public
     */
    public function edit_response($ticket, $response, $oldticket, $oldresponse)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $response->getVar('ticketid'));
        $logMessage->setVar('lastUpdated', $response->getVar('updateTime'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_EDIT_RESPONSE, $response->getVar('id')));

        return $this->_hLog->insert($logMessage);
    }

    /**
     * Add Log Events for 'batch_dept' event
     * @param  array           $tickets Array of Xhelp\Ticket objects
     * @param  Xhelp\Department $dept    New department for tickets
     * @return bool            True on success, false on error
     * @access public
     */
    public function batch_dept($tickets, $dept)
    {
        global $xoopsUser;

        $hDept   = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
        $deptObj = $hDept->get($dept);

        foreach ($tickets as $ticket) {
            $logMessage = $this->_hLog->create();
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
     * @param  array $tickets  Array of Xhelp\Ticket objects
     * @param  int   $priority New priority level for tickets
     * @return bool  True on success, false on error
     * @access public
     */
    public function batch_priority($tickets, $priority)
    {
        global $xoopsUser;

        $priority = (int)$priority;
        foreach ($tickets as $ticket) {
            $logMessage = $this->_hLog->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
            $logMessage->setVar('posted', $ticket->getVar('posted'));
            $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_PRIORITY, $ticket->getVar('priority'), $priority));
            $this->_hLog->insert($logMessage);
        }

        return true;
    }

    /**
     * Add Log Events for 'batch_owner' event
     * @param  array $tickets Array of Xhelp\Ticket objects
     * @param  int   $owner   New owner for tickets
     * @return bool  True on success, false on error
     * @access public
     */
    public function batch_owner($tickets, $owner)
    {
        global $xoopsUser;

        $updated   = time();
        $ownername = ($xoopsUser->getVar('uid') == $owner ? $xoopsUser->getVar('uname') : $xoopsUser::getUnameFromId($owner));
        foreach ($tickets as $ticket) {
            $logMessage = $this->_hLog->create();
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
     * @param  array $tickets   Array of Xhelp\Ticket objects
     * @param  int   $newstatus New status for tickets
     * @return bool  True on success, false on error
     * @access public
     */
    public function batch_status($tickets, $newstatus)
    {
        global $xoopsUser;

        $updated = time();
        $sStatus = Xhelp\Utility::getStatus($newstatus);
        $uid     = $xoopsUser->getVar('uid');
        foreach ($tickets as $ticket) {
            $logMessage = $this->_hLog->create();
            $logMessage->setVar('uid', $uid);
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $updated);
            $logMessage->setVar('action', sprintf(_XHELP_LOG_UPDATE_STATUS, Xhelp\Utility::getStatus($ticket->getVar('status')), $sStatus));
            $this->_hLog->insert($logMessage, true);
            unset($logMessage);
        }

        return true;
    }

    /**
     * Event: batch_response
     * Triggered after a batch response addition
     * Note: the $response->getVar('ticketid') field is empty for this function
     * @param array          $tickets  The Xhelp\Ticket objects that were modified
     * @param Xhelp\Responses $response The response added to each ticket
     * @return bool
     */
    public function batch_response($tickets, $response)
    {
        global $xoopsUser;

        $updateTime = time();
        $uid        = $xoopsUser->getVar('uid');

        foreach ($tickets as $ticket) {
            $logMessage = $this->_hLog->create();
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
     * @param  int $ticketid      First ticket being merged
     * @param  int $mergeTicketid Second ticket being merged
     * @param  int $newTicket     Resulting merged ticket
     * @return bool True on success, false on error
     * @access public
     */
    public function merge_tickets($ticketid, $mergeTicketid, $newTicket)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticketid);
        $logMessage->setVar('action', sprintf(_XHELP_LOG_MERGETICKETS, $mergeTicketid, $ticketid));
        $logMessage->setVar('lastUpdated', time());
        if ($this->_hLog->insert($logMessage)) {
            return true;
        }

        return false;
    }

    /**
     * Add Log Events for 'delete_file' event
     * @param  Xhelp\File $file File being deleted
     * @return bool      True on success, false on error
     * @access public
     */
    public function delete_file($file)
    {
        global $xoopsUser;

        $filename = $file->getVar('filename');

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $file->getVar('ticketid'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_DELETEFILE, $filename));
        $logMessage->setVar('lastUpdated', time());

        if ($this->_hLog->insert($logMessage, true)) {
            return true;
        }

        return false;
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param Xhelp\Ticket $ticket Ticket used as base for FAQ
     * @param Xhelp\Faq    $faq    FAQ that was added
     * @return
     */
    public function new_faq($ticket, $faq)
    {
        global $xoopsUser;

        $logMessage = $this->_hLog->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('action', sprintf(_XHELP_LOG_NEWFAQ, $faq->getVar('subject')));

        return $this->_hLog->insert($logMessage, true);
    }

    /**
     * Only have 1 instance of class used
     * @return object {@link xhelp_eventService}
     * @access  public
     */

    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }
}
