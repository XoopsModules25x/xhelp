<?php
//

require_once XHELP_CLASS_PATH . '/xhelpService.php';

/**
 * xhelpFirnService class
 *
 * Trains the FIRN (Find It Right Now) service
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class XHelpFirnService extends xhelpService
{
    /**
     * XHelpFirnService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    public function _attachEvents()
    {
        $this->_attachEvent('new_faq', $this);
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param xhelpTicket $ticket Ticket used as base for FAQ
     * @param xhelpFaq    $faq    FAQ that was added
     */
    public function new_faq($ticket, $faq)
    {
        global $xoopsUser;

        //Create a new solution from the supplied ticket / faq
        $hTicketSol = xhelpGetHandler('ticketSolution');
        $sol        = $hTicketSol->create();

        $sol->setVar('ticketid', $ticket->getVar('id'));
        $sol->setVar('url', $faq->getVar('url'));
        $sol->setVar('title', $faq->getVar('subject'));
        $sol->setVar('uid', $xoopsUser->getVar('uid'));

        return $hTicketSol->addSolution($ticket, $sol);
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
