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

    /**
     *
     */
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
        $helper = Helper::getInstance();

        //Create a new solution from the supplied ticket / faq
        /** @var \XoopsModules\Xhelp\TicketSolutionHandler $ticketSolutionHandler */
        $ticketSolutionHandler = $helper->getHandler('TicketSolution');
        /** @var \XoopsModules\Xhelp\TicketSolution $ticketSolution */
        $ticketSolution = $ticketSolutionHandler->create();

        $ticketSolution->setVar('ticketid', $ticket->getVar('id'));
        $ticketSolution->setVar('url', $faq->getVar('url'));
        $ticketSolution->setVar('title', $faq->getVar('subject'));
        $ticketSolution->setVar('uid', $xoopsUser->getVar('uid'));

        return $ticketSolutionHandler->addSolution($ticket, $ticketSolution);
    }

    /**
     * Only have 1 instance of class used
     * @return Service {@link Service}
     */
    public static function getInstance(): Service
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }
}
