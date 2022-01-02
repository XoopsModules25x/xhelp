<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * TicketMailParser class
 *
 * Part of the email submission subsystem. Converts a parsed email into a ticket
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @depreciated
 */


/**
 * Class TicketMailParser
 */
class TicketMailParser
{
    /**
     * Instance of Ticket Object
     */
    public $_ticket;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        //any inits?
    }

    /**
     * Create a new ticket object
     * @param mixed $mailParser
     * @param mixed $xoopsUser
     * @param mixed $department
     * @param mixed $server
     * @return bool
     */
    public function createTicket($mailParser, $xoopsUser, $department, $server): bool
    {
        //get ticket handler
        $ticketHandler = new TicketHandler($GLOBALS['xoopsDB']);
        $ticket        = $ticketHandler->create();

        $ticket->setVar('uid', $xoopsUser->uid());
        $ticket->setVar('subject', $mailParser->getSubject());
        $ticket->setVar('department', $department->getVar('id'));
        $ticket->setVar('description', $mailParser->getBody());
        $ticket->setVar('priority', 3);
        $ticket->setVar('posted', \time());
        $ticket->setVar('userIP', \_AM_XHELP_EMAIL_SCANNER_IP_COLUMN);
        $ticket->setVar('serverid', $server->getVar('id'));
        $ticket->createEmailHash($mailParser->getEmail());

        if ($ticketHandler->insert($ticket)) {
            $this->_ticket = $ticket;

            return true;
        }

        return false;
    }

    /**
     * Returns the ticket object for this email
     * @return object {@link Ticket} Ticket Object
     */
    public function &getTicket()
    {
        return $this->_ticket;
    }
}
