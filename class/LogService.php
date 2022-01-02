<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

// require_once XHELP_CLASS_PATH . '/Service.php';

/**
 * xhelp_logService class
 *
 * Part of the Messaging Subsystem.  Uses the LogMessageHandler class for logging
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class LogService extends Service
{
    /**
     * Instance of the LogMessageHandler
     *
     * @var object
     */
    public $logMessageHandler;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->logMessageHandler = Helper::getInstance()->getHandler('LogMessage');
        $this->init();
    }

    public function attachEvents()
    {
        $this->attachEvent('batch_dept', $this);
        $this->attachEvent('batch_owner', $this);
        $this->attachEvent('batch_priority', $this);
        $this->attachEvent('batch_response', $this);
        $this->attachEvent('batch_status', $this);
        $this->attachEvent('close_ticket', $this);
        $this->attachEvent('delete_file', $this);
        $this->attachEvent('edit_response', $this);
        $this->attachEvent('edit_ticket', $this);
        $this->attachEvent('merge_tickets', $this);
        $this->attachEvent('new_response', $this);
        $this->attachEvent('new_response_rating', $this);
        $this->attachEvent('new_ticket', $this);
        $this->attachEvent('reopen_ticket', $this);
        $this->attachEvent('update_owner', $this);
        $this->attachEvent('update_priority', $this);
        $this->attachEvent('update_status', $this);
        $this->attachEvent('new_faq', $this);
    }

    /**
     * Callback function for the 'new_ticket' event
     * @param Ticket $ticket Ticket that was added
     * @return bool        True on success, false on error
     */
    public function new_ticket($ticket): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $ticket->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('posted'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));

        if ($xoopsUser->getVar('uid') == $ticket->getVar('uid')) {
            $logMessage->setVar('action', \_XHELP_LOG_ADDTICKET);
        } else {
            // Will display who logged the ticket for the user
            $logMessage->setVar('action', \sprintf(\_XHELP_LOG_ADDTICKET_FORUSER, $xoopsUser::getUnameFromId($ticket->getVar('uid')), $xoopsUser->getVar('uname')));
        }

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the 'update_priority' event
     * @param Ticket $ticket      Ticket that was modified
     * @param int          $oldpriority Original ticket priority
     * @return bool        True on success, false on error
     */
    public function update_priority($ticket, $oldpriority): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_UPDATE_PRIORITY, $oldpriority, $ticket->getVar('priority')));

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the 'update_status' event
     * @param Ticket $ticket    Ticket that was modified
     * @param Status $oldstatus Original ticket status
     * @param Status $newstatus New ticket status
     * @return bool        True on success, false on error
     */
    public function update_status($ticket, $oldstatus, $newstatus): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('posted', $ticket->getVar('posted'));
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_UPDATE_STATUS, $oldstatus->getVar('description'), $newstatus->getVar('description')));

        return $this->logMessageHandler->insert($logMessage, true);
    }

    /**
     * Event: update_owner
     * Triggered after ticket ownership change (Individual)
     * Also See: batch_owner
     * @param Ticket $ticket   Ticket that was changed
     * @param int          $oldowner UID of previous owner
     * @param int          $newowner UID of new owner
     * @return
     */
    public function update_owner($ticket, $oldowner, $newowner)
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        if ($xoopsUser->getVar('uid') == $ticket->getVar('ownership')) {
            //User claimed ownership
            $logMessage->setVar('action', \_XHELP_LOG_CLAIM_OWNERSHIP);
        } else {
            //Ownership was assigned
            $logMessage->setVar('action', \sprintf(\_XHELP_LOG_ASSIGN_OWNERSHIP, $xoopsUser::getUnameFromId($ticket->getVar('ownership'))));
        }

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the reopen_ticket event
     * @param Ticket $ticket Ticket that was re-opened
     * @return bool        True on success, false on error
     */
    public function reopen_ticket($ticket): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('action', \_XHELP_LOG_REOPEN_TICKET);

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the close_ticket event
     * @param Ticket $ticket Ticket that was closed
     * @return bool        True on success, false on error
     */
    public function close_ticket($ticket): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
        $logMessage->setVar('action', \_XHELP_LOG_CLOSE_TICKET);

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Add Log information for 'new_response' event
     * @param Ticket    $ticket      Ticket for Response
     * @param Responses $newResponse Response that was added
     * @return bool           True on success, false on error
     */
    public function new_response($ticket, $newResponse): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('action', \_XHELP_LOG_ADDRESPONSE);
        $logMessage->setVar('lastUpdated', $newResponse->getVar('updateTime'));

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the 'new_response_rating' event
     * @param Rating    $rating   Rating Information
     * @param Ticket    $ticket   Ticket for Rating
     * @param Responses $response Response that was rated
     * @return bool           True on success, false on error
     */
    public function new_response_rating($rating, $ticket, $response): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $rating->getVar('ticketid'));
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_ADDRATING, $rating->getVar('responseid')));
        $logMessage->setVar('lastUpdated', \time());

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the 'edit_ticket' event
     * @param Ticket $oldTicket  Original Ticket Information
     * @param Ticket $ticketInfo New Ticket Information
     * @return bool        True on success, false on error
     */
    public function edit_ticket($oldTicket, $ticketInfo): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticketInfo->getVar('id'));
        $logMessage->setVar('lastUpdated', $ticketInfo->getVar('posted'));
        $logMessage->setVar('posted', $ticketInfo->getVar('posted'));
        $logMessage->setVar('action', \_XHELP_LOG_EDITTICKET);

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Callback function for the 'edit_response' event
     * @param $ticket
     * @param $response
     * @param $oldticket
     * @param $oldresponse
     * @return bool True on success, false on error
     * @internal param array $args Array of arguments passed to EventService
     */
    public function edit_response($ticket, $response, $oldticket, $oldresponse): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $response->getVar('ticketid'));
        $logMessage->setVar('lastUpdated', $response->getVar('updateTime'));
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_EDIT_RESPONSE, $response->getVar('id')));

        return $this->logMessageHandler->insert($logMessage);
    }

    /**
     * Add Log Events for 'batch_dept' event
     * @param array                $tickets Array of Ticket objects
     * @param Department|int $dept    New department for tickets
     * @return bool            True on success, false on error
     */
    public function batch_dept($tickets, $dept): bool
    {
        global $xoopsUser;

        $departmentHandler = new DepartmentHandler($GLOBALS['xoopsDB']);
        $deptObj           = $departmentHandler->get($dept);

        foreach ($tickets as $ticket) {
            $logMessage = $this->logMessageHandler->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', \time());
            $logMessage->setVar('action', \sprintf(\_XHELP_LOG_SETDEPT, $deptObj->getVar('department')));
            $this->logMessageHandler->insert($logMessage);
            unset($logMessage);
        }

        return true;
    }

    /**
     * Add Log Events for 'batch_priority' event
     * @param array $tickets  Array of Ticket objects
     * @param int   $priority New priority level for tickets
     * @return bool  True on success, false on error
     */
    public function batch_priority($tickets, $priority): bool
    {
        global $xoopsUser;

        $priority = (int)$priority;
        foreach ($tickets as $ticket) {
            $logMessage = $this->logMessageHandler->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $ticket->getVar('lastUpdated'));
            $logMessage->setVar('posted', $ticket->getVar('posted'));
            $logMessage->setVar('action', \sprintf(\_XHELP_LOG_UPDATE_PRIORITY, $ticket->getVar('priority'), $priority));
            $this->logMessageHandler->insert($logMessage);
        }

        return true;
    }

    /**
     * Add Log Events for 'batch_owner' event
     * @param array $tickets Array of Ticket objects
     * @param int   $owner   New owner for tickets
     * @return bool  True on success, false on error
     */
    public function batch_owner($tickets, $owner): bool
    {
        global $xoopsUser;

        $updated   = \time();
        $ownername = ($xoopsUser->getVar('uid') == $owner ? $xoopsUser->getVar('uname') : $xoopsUser::getUnameFromId($owner));
        foreach ($tickets as $ticket) {
            $logMessage = $this->logMessageHandler->create();
            $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $updated);
            if ($xoopsUser->getVar('uid') == $owner) {
                $logMessage->setVar('action', \_XHELP_LOG_CLAIM_OWNERSHIP);
            } else {
                $logMessage->setVar('action', \sprintf(\_XHELP_LOG_ASSIGN_OWNERSHIP, $ownername));
            }
            $this->logMessageHandler->insert($logMessage);
            unset($logMessage);
        }

        return true;
    }

    /**
     * Add Log Events for 'batch_status' event
     * @param array $tickets   Array of Ticket objects
     * @param int   $newstatus New status for tickets
     * @return bool  True on success, false on error
     */
    public function batch_status($tickets, $newstatus): bool
    {
        global $xoopsUser;

        $updated = \time();
        $sStatus = Utility::getStatus($newstatus);
        $uid     = $xoopsUser->getVar('uid');
        foreach ($tickets as $ticket) {
            $logMessage = $this->logMessageHandler->create();
            $logMessage->setVar('uid', $uid);
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('lastUpdated', $updated);
            $logMessage->setVar('action', \sprintf(\_XHELP_LOG_UPDATE_STATUS, Utility::getStatus($ticket->getVar('status')), $sStatus));
            $this->logMessageHandler->insert($logMessage, true);
            unset($logMessage);
        }

        return true;
    }

    /**
     * Event: batch_response
     * Triggered after a batch response addition
     * Note: the $response->getVar('ticketid') field is empty for this function
     * @param array           $tickets  The Ticket objects that were modified
     * @param Responses $response The response added to each ticket
     * @return bool
     */
    public function batch_response($tickets, $response): bool
    {
        global $xoopsUser;

        $updateTime = \time();
        $uid        = $xoopsUser->getVar('uid');

        foreach ($tickets as $ticket) {
            $logMessage = $this->logMessageHandler->create();
            $logMessage->setVar('uid', $uid);
            $logMessage->setVar('ticketid', $ticket->getVar('id'));
            $logMessage->setVar('action', \_XHELP_LOG_ADDRESPONSE);
            $logMessage->setVar('lastUpdated', $updateTime);
            $this->logMessageHandler->insert($logMessage);
        }

        return true;
    }

    /**
     * Add Log Events for 'merge_tickets' event
     * @param int $ticketid      First ticket being merged
     * @param int $mergeTicketid Second ticket being merged
     * @param int $newTicket     Resulting merged ticket
     * @return bool True on success, false on error
     */
    public function merge_tickets($ticketid, $mergeTicketid, $newTicket): bool
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticketid);
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_MERGETICKETS, $mergeTicketid, $ticketid));
        $logMessage->setVar('lastUpdated', \time());
        if ($this->logMessageHandler->insert($logMessage)) {
            return true;
        }

        return false;
    }

    /**
     * Add Log Events for 'delete_file' event
     * @param File $file File being deleted
     * @return bool      True on success, false on error
     */
    public function delete_file($file): bool
    {
        global $xoopsUser;

        $filename = $file->getVar('filename');

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $file->getVar('ticketid'));
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_DELETEFILE, $filename));
        $logMessage->setVar('lastUpdated', \time());

        if ($this->logMessageHandler->insert($logMessage, true)) {
            return true;
        }

        return false;
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param Ticket $ticket Ticket used as base for FAQ
     * @param Faq    $faq    FAQ that was added
     * @return
     */
    public function new_faq($ticket, $faq)
    {
        global $xoopsUser;

        $logMessage = $this->logMessageHandler->create();
        $logMessage->setVar('uid', $xoopsUser->getVar('uid'));
        $logMessage->setVar('ticketid', $ticket->getVar('id'));
        $logMessage->setVar('action', \sprintf(\_XHELP_LOG_NEWFAQ, $faq->getVar('subject')));

        return $this->logMessageHandler->insert($logMessage, true);
    }

    /**
     * Only have 1 instance of class used
     * @return EventService {@link EventService}
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
