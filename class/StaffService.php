<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

use Xmf\Request;

/**
 * xhelp_staffService class
 *
 * Part of the Messaging Subsystem.  Updates staff member information.
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class StaffService extends Service
{
    /**
     * Instance of the xoopsStaffHandler
     *
     * @var object
     */
    public $staffHandler;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->helper       = Helper::getInstance();
        $this->staffHandler = $this->helper->getHandler('Staff');
        $this->init();
    }

    /**
     * Update staff response time if first staff response
     * @param Ticket   $ticket   Ticket for response
     * @param Response $response Response
     */
    public function new_response(Ticket $ticket, Response $response)
    {
        global $xoopsUser;

        //if first response for ticket, update staff responsetime
        /** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
        $responseHandler = $this->helper->getHandler('Response');
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $this->helper->getHandler('Membership');
        if (1 == $responseHandler->getStaffResponseCount($ticket->getVar('id'))) {
            if ($membershipHandler->isStaffMember($response->getVar('uid'), $ticket->getVar('department'))) {
                $responseTime = \abs($response->getVar('updateTime') - $ticket->getVar('posted'));
                $this->staffHandler->updateResponseTime($response->getVar('uid'), $responseTime);
            }
        }
    }

    /**
     * Update staff response time if first staff response
     * @param array    $tickets
     * @param Response $response Response
     * @internal param Ticket $ticket Ticket for response
     * @internal param int $timespent Number of minutes spent on ticket
     * @internal param bool $private Is the response private?
     */
    public function batch_response(array $tickets, Response $response)
    {
        global $xoopsUser;

        $update          = \time();
        $uid             = $xoopsUser->getVar('uid');
        $responseHandler = $this->helper->getHandler('Response');
        foreach ($tickets as $ticket) {
            //if first response for ticket, update staff responsetime

            $membershipHandler = $this->helper->getHandler('Membership');
            if (1 == $responseHandler->getStaffResponseCount($ticket->getVar('id'))) {
                $responseTime = \abs($update - $ticket->getVar('posted'));
                $this->staffHandler->updateResponseTime($uid, $responseTime);
            }
        }
    }

    /**
     * Handler for the 'batch_status' event
     * @param array  $tickets   Array of Ticket objects
     * @param Status $newstatus New Status of all tickets
     */
    public function batch_status(array $tickets, Status $newstatus)
    {
        global $xoopsUser;

        $uid = $xoopsUser->getVar('uid');

        if (\XHELP_STATE_RESOLVED == $newstatus->getVar('state')) {
            $this->staffHandler->increaseCallsClosed($uid, \count($tickets));
        }
    }

    /**
     * Callback function for the 'close_ticket' event
     * @param Ticket $ticket Closed ticket
     * @return bool        True on success, false on error
     */
    public function close_ticket(Ticket $ticket): bool
    {
        global $xoopsUser;

        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $this->helper->getHandler('Membership');
        if ($membershipHandler->isStaffMember($ticket->getVar('closedBy'), $ticket->getVar('department'))) {
            $this->staffHandler->increaseCallsClosed($ticket->getVar('closedBy'), 1);
        }

        return true;
    }

    /**
     * Callback function for the 'reopen_ticket' event
     * @param Ticket $ticket
     * @return bool True on success, false on error
     * @internal param array $args Array of arguments passed to EventService
     */
    public function reopen_ticket(Ticket $ticket): bool
    {
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $this->helper->getHandler('Membership');
        if ($membershipHandler->isStaffMember($ticket->getVar('closedBy'), $ticket->getVar('department'))) {
            $this->staffHandler->increaseCallsClosed($ticket->getVar('closedBy'), -1);
        }

        return true;
    }

    /**
     * Callback function for the 'new_response_rating' event
     * @param \XoopsModules\Xhelp\StaffReview $rating   Rating
     * @param Ticket                          $ticket   Ticket that was rated
     * @param Response                        $response Response that was rated
     * @return bool          True on success, false on error
     */
    public function new_response_rating(StaffReview $rating, Ticket $ticket, Response $response): bool
    {
        global $xoopsUser;

        $staffHandler = $this->helper->getHandler('Staff');

        return $staffHandler->updateRating($rating->getVar('staffid'), $rating->getVar('rating'));
    }

    /**
     * Event Handler for 'view_ticket'
     * @param Ticket $ticket Ticket being viewd
     */
    public function view_ticket(Ticket $ticket)
    {
        $value = [];

        //Store a list of recent tickets in the xhelp_recent_tickets cookie
        if (Request::hasVar('xhelp_recent_tickets', 'COOKIE')) {
            $oldvalue = \explode(',', $_COOKIE['xhelp_recent_tickets']);
        } else {
            $oldvalue = [];
        }

        $value[] = $ticket->getVar('id');

        $value = \array_merge($value, $oldvalue);
        $value = $this->uniqueArray($value);
        $value = \array_slice($value, 0, 5);
        //Keep this value for 15 days
        setcookie('xhelp_recent_tickets', \implode(',', $value), \time() + 15 * 24 * 60 * 60, '/');
    }

    /**
     * Event Handler for 'delete_staff' event
     * @param Staff $staff Staff member being deleted
     * @return bool       True on success, false on error
     */
    public function delete_staff(Staff $staff): bool
    {
        $ticketHandler = $this->helper->getHandler('Ticket');

        return $ticketHandler->updateAll('ownership', 0, new \Criteria('ownership', $staff->getVar('uid')));
    }

    /**
     * Only have 1 instance of class used
     * @return StaffService {@link StaffService}
     */
    public static function getInstance(): StaffService
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * @param array $array
     * @return array
     */
    private function uniqueArray(array $array): array
    {
        $out = [];

        //    loop through the inbound
        foreach ($array as $key => $value) {
            //    if the item isn't in the array
            if (!\in_array($value, $out)) { //    add it to the array
                $out[$key] = $value;
            }
        }

        return $out;
    }

    public function attachEvents()
    {
        $this->attachEvent('batch_response', $this);
        $this->attachEvent('batch_status', $this);
        $this->attachEvent('close_ticket', $this);
        $this->attachEvent('delete_staff', $this);
        $this->attachEvent('new_response', $this);
        $this->attachEvent('new_response_rating', $this);
        $this->attachEvent('reopen_ticket', $this);
        $this->attachEvent('view_ticket', $this);
    }
}
