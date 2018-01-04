<?php namespace XoopsModules\Xhelp;

/**
 * Xhelp\TicketMailParser class
 *
 * Part of the email submission subsystem. Converts a parsed email into a ticket
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @access  public
 * @depreciated
 * @package xhelp
 */

use XoopsModules\Xhelp;

class TicketMailParser
{
    /**
     * Instance of Ticket Object
     * @access private
     */
    public $_ticket;

    /**
     * Class Constructor
     * @access public
     */
    public function __construct()
    {
        //any inits?
    }

    /**
     * Create a new ticket object
     * @param object Reference to a {@link Xhelp\EmailParser} object
     * @param object Current {@link xoopsUser} object
     * @param object {@link Xhelp\Department} Ticket Department
     * @param object {@link Xhelp\DepartmentEmailServer} Originating Email Server
     * @return bool
     * @access public
     */
    public function createTicket(&$mailParser, &$xoopsUser, &$department, &$server)
    {
        //get ticket handler
        $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
        $ticket  = $hTicket->create();
        //
        $ticket->setVar('uid', $xoopsUser->uid());
        $ticket->setVar('subject', $mailParser->getSubject());
        $ticket->setVar('department', $department->getVar('id'));
        $ticket->setVar('description', $mailParser->getBody());
        $ticket->setVar('priority', 3);
        $ticket->setVar('posted', time());
        $ticket->setVar('userIP', _XHELP_EMAIL_SCANNER_IP_COLUMN);
        $ticket->setVar('serverid', $server->getVar('id'));
        $ticket->createEmailHash($mailParser->getEmail());
        //
        if ($hTicket->insert($ticket)) {
            $this->_ticket = $ticket;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the ticket object for this email
     * @return object {@link Xhelp\Ticket} Ticket Object
     */
    public function &getTicket()
    {
        return $this->_ticket;
    }
}
