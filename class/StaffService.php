<?php namespace Xoopsmodules\xhelp;

//

use Xoopsmodules\xhelp;

/**
 * xhelp_staffService class
 *
 * Part of the Messaging Subsystem.  Updates staff member information.
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class StaffService extends xhelp\Service
{
    /**
     * Instance of the xoopsStaffHandler
     *
     * @var object
     * @access  private
     */
    public $_hStaff;

    /**
     * Class Constructor
     *
     * @access  public
     */
    public function __construct()
    {
        $this->_hStaff = new xhelp\StaffHandler($GLOBALS['xoopsDB']);
        $this->init();
    }

    /**
     * Update staff response time if first staff response
     * @param  xhelp\Ticket    $ticket   Ticket for response
     * @param  xhelp\Responses $response Response
     * @return bool           True on success, false on error
     * @access public
     */
    public function new_response($ticket, $response)
    {
        global $xoopsUser;

        //if first response for ticket, update staff responsetime
        $hResponse   = new xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        $hMembership = new xhelp\MembershipHandler($GLOBALS['xoopsDB']);
        if (1 == $hResponse->getStaffResponseCount($ticket->getVar('id'))) {
            if ($hMembership->isStaffMember($response->getVar('uid'), $ticket->getVar('department'))) {
                $responseTime = abs($response->getVar('updateTime') - $ticket->getVar('posted'));
                $this->_hStaff->updateResponseTime($response->getVar('uid'), $responseTime);
            }
        }
    }

    /**
     * Update staff response time if first staff response
     * @param                 $tickets
     * @param  xhelp\Responses $response Response
     * @return bool True on success, false on error
     * @internal param xhelp\Ticket $ticket Ticket for response
     * @internal param int $timespent Number of minutes spent on ticket
     * @internal param bool $private Is the response private?
     * @access   public
     */
    public function batch_response($tickets, $response)
    {
        global $xoopsUser;

        $update    = time();
        $uid       = $xoopsUser->getVar('uid');
        $hResponse = new xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        foreach ($tickets as $ticket) {
            //if first response for ticket, update staff responsetime

            $hMembership = new xhelp\MembershipHandler($GLOBALS['xoopsDB']);
            if (1 == $hResponse->getStaffResponseCount($ticket->getVar('id'))) {
                $responseTime = abs($update - $ticket->getVar('posted'));
                $this->_hStaff->updateResponseTime($uid, $responseTime);
            }
        }
    }

    /**
     * Handler for the 'batch_status' event
     * @param  array $tickets   Array of xhelp\Ticket objects
     * @param  int   $newstatus New Status of all tickets
     * @return bool  True on success, false on error
     * @access public
     */
    public function batch_status($tickets, $newstatus)
    {
        global $xoopsUser;

        $uid = $xoopsUser->getVar('uid');

        if (XHELP_STATE_RESOLVED == $newstatus->getVar('state')) {
            $this->_hStaff->increaseCallsClosed($uid, count($tickets));
        }
    }

    /**
     * Callback function for the 'close_ticket' event
     * @param  xhelp\Ticket $ticket Closed ticket
     * @return bool        True on success, false on error
     * @access public
     */
    public function close_ticket($ticket)
    {
        global $xoopsUser;

        $hMembership = new xhelp\MembershipHandler($GLOBALS['xoopsDB']);
        if ($hMembership->isStaffMember($ticket->getVar('closedBy'), $ticket->getVar('department'))) {
            $this->_hStaff->increaseCallsClosed($ticket->getVar('closedBy'), 1);
        }

        return true;
    }

    /**
     * Callback function for the 'reopen_ticket' event
     * @param $ticket
     * @return bool True on success, false on error
     * @internal param array $args Array of arguments passed to EventService
     * @access   public
     */
    public function reopen_ticket($ticket)
    {
        $hMembership = new xhelp\MembershipHandler($GLOBALS['xoopsDB']);
        if ($hMembership->isStaffMember($ticket->getVar('closedBy'), $ticket->getVar('department'))) {
            $this->_hStaff->increaseCallsClosed($ticket->getVar('closedBy'), -1);
        }

        return true;
    }

    /**
     * Callback function for the 'new_response_rating' event
     * @param  xhelp\Rating   $rating   Rating
     * @param  xhelp\Ticket   $ticket   Ticket that was rated
     * @param  xhelpResponse $response Response that was rated
     * @return bool          True on success, false on error
     * @access public
     */
    public function new_response_rating($rating, $ticket, $response)
    {
        global $xoopsUser;

        $hStaff = new xhelp\StaffHandler($GLOBALS['xoopsDB']);

        return $hStaff->updateRating($rating->getVar('staffid'), $rating->getVar('rating'));
    }

    /**
     * Event Handler for 'view_ticket'
     * @param  xhelp\Ticket $ticket Ticket being viewd
     * @return bool        True on success, false on error
     * @access public
     */
    public function view_ticket($ticket)
    {
        $value = [];

        //Store a list of recent tickets in the xhelp_recent_tickets cookie
        if (isset($_COOKIE['xhelp_recent_tickets'])) {
            $oldvalue = explode(',', $_COOKIE['xhelp_recent_tickets']);
        } else {
            $oldvalue = [];
        }

        $value[] = $ticket->getVar('id');

        $value = array_merge($value, $oldvalue);
        $value = $this->_array_unique($value);
        $value = array_slice($value, 0, 5);
        //Keep this value for 15 days
        setcookie('xhelp_recent_tickets', implode(',', $value), time() + 15 * 24 * 60 * 60, '/');
    }

    /**
     * Event Handler for 'delete_staff' event
     * @param  xhelp\Staff $staff Staff member being deleted
     * @return bool       True on success, false on error
     * @access public
     */
    public function delete_staff($staff)
    {
        $hTicket = new xhelp\TicketHandler($GLOBALS['xoopsDB']);

        return $hTicket->updateAll('ownership', 0, new \Criteria('ownership', $staff->getVar('uid')));
    }

    /**
     * Only have 1 instance of class used
     * @return object {@link xhelp_staffService}
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

    /**
     * @param $array
     * @return array
     */
    public function _array_unique($array)
    {
        $out = [];

        //    loop through the inbound
        foreach ($array as $key => $value) {
            //    if the item isn't in the array
            if (!in_array($value, $out)) { //    add it to the array
                $out[$key] = $value;
            }
        }

        return $out;
    }

    public function _attachEvents()
    {
        $this->_attachEvent('batch_response', $this);
        $this->_attachEvent('batch_status', $this);
        $this->_attachEvent('close_ticket', $this);
        $this->_attachEvent('delete_staff', $this);
        $this->_attachEvent('new_response', $this);
        $this->_attachEvent('new_response_rating', $this);
        $this->_attachEvent('reopen_ticket', $this);
        $this->_attachEvent('view_ticket', $this);
    }
}
