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
 * @author       Nazar Aziz <nazar@panthersoftware.com>
 * @author       XOOPS Development Team
 */

/**
 * TicketMailParser class
 *
 * Part of the email submission subsystem. Converts a parsed email into a ticket
 *
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
        $helper = Helper::getInstance();
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');
        /** @var \XoopsModules\Xhelp\Ticket $ticket */
        $ticket = $ticketHandler->create();

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
    public function &getTicket(): object
    {
        return $this->_ticket;
    }
}
