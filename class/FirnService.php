<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

// require_once XHELP_CLASS_PATH . '/Service.php';

/**
 * FirnService class
 *
 * Trains the FIRN (Find It Right Now) service
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class FirnService extends Service
{
    /**
     * FirnService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    public function attachEvents()
    {
        $this->attachEvent('new_faq', $this);
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param Ticket $ticket Ticket used as base for FAQ
     * @param Faq    $faq    FAQ that was added
     * @return bool
     */
    public function new_faq(Ticket $ticket, Faq $faq): bool
    {
        global $xoopsUser;

        //Create a new solution from the supplied ticket / faq
        $hTicketSol = new TicketSolutionHandler($GLOBALS['xoopsDB']);
        $sol        = $hTicketSol->create();

        $sol->setVar('ticketid', $ticket->getVar('id'));
        $sol->setVar('url', $faq->getVar('url'));
        $sol->setVar('title', $faq->getVar('subject'));
        $sol->setVar('uid', $xoopsUser->getVar('uid'));

        return $hTicketSol->addSolution($ticket, $sol);
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
