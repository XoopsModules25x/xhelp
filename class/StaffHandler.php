<?php namespace XoopsModules\Xhelp;

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
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
// require_once XHELP_CLASS_PATH . '/NotificationService.php';

/**
 * Xhelp\Staff class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */

// require_once XHELP_CLASS_PATH . '/session.php';


/**
 * Xhelp\StaffHandler class
 *
 * Staff Handler for Xhelp\Staff class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class StaffHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = Staff::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_staff';

    /**
     * Constructor
     *
     * @param null|\XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::init($db);
    }

    /**
     * retrieve a staff object from the database
     * @param  int $uid user id
     * @return bool <a href='psi_element://Xhelp\Staff'>Xhelp\Staff</a>
     * @access public
     */
    public function &getByUid($uid)
    {
        $ret = false;
        $uid = (int)$uid;
        if ($uid > 0) {
            $sql = $this->_selectQuery(new \Criteria('uid', $uid));
            if (!$result = $this->_db->query($sql)) {
                return $ret;
            }
            if ($arr = $this->_db->fetchArray($result)) {
                $ret = new $this->classname($arr);

                return $ret;
            }
        }

        return $ret;
    }

    /**
     * Add user to a new role
     *
     * @param int $uid    user id
     * @param int $roleid role id
     * @param int $deptid department id
     *
     * @return TRUE if success, FALSE if failure
     * @access public
     */
    public function addStaffRole($uid, $roleid, $deptid)
    {
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);
        $role       = $hStaffRole->create();
        $role->setVar('uid', $uid);
        $role->setVar('roleid', $roleid);
        $role->setVar('deptid', $deptid);
        if (!$hStaffRole->insert($role)) {
            return false;
        }

        return true;
    }

    /**
     * Retrive all of the roles of current staff member
     *
     * @param      $uid
     * @param bool $id_as_key
     * @return array|bool <a href='psi_element://Xhelp\StaffRoles'>Xhelp\StaffRoles</a>, FALSE if failure
     * FALSE if failure
     * @access public
     */
    public function &getRoles($uid, $id_as_key = false)
    {
        $uid        = (int)$uid;
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);

        if (!$roles = $hStaffRole->getObjectsByStaff($uid, $id_as_key)) {
            return false;
        }

        return $roles;
    }

    /**
     * @return bool
     */
    public function clearRoles()
    {
        $_xhelpSession = new Xhelp\Session();

        if ($myRoles = $_xhelpSession->get('xhelp_hasRights')) {
            $_xhelpSession->del('xhelp_hasRights');

            return true;
        }

        return false;
    }

    /**
     * Retrieve all of the roles of current department for staff member
     *
     * @param      $uid
     * @param      $deptid
     * @param bool $id_as_key
     * @return array|bool <a href='psi_element://Xhelp\StaffRoles'>Xhelp\StaffRoles</a>, FALSE if failure
     * FALSE if failure
     * @access public
     */
    public function &getRolesByDept($uid, $deptid, $id_as_key = false)
    {
        $uid        = (int)$uid;
        $deptid     = (int)$deptid;
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);

        $crit = new \CriteriaCompo(new \Criteria('uid', $uid));
        $crit->add(new \Criteria('deptid', $deptid));

        if (!$roles = $hStaffRole->getObjects($crit, $id_as_key)) {
            return false;
        }

        return $roles;
    }

    /**
     * Remove user from a role
     *
     * @param int $uid user id
     * @return TRUE if success, FALSE if failure
     * @internal param int $roleid role id
     * @internal param int $deptid department id
     *
     * @access   public
     */
    public function removeStaffRoles($uid)
    {
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);
        $crit       = new \Criteria('uid', $uid);

        return $hStaffRole->deleteAll($crit);
    }

    /**
     * Check if a user is in a particular role
     *
     * @param int $uid    user id
     * @param int $roleid role id
     *
     * @return TRUE on success, FALSE on failure
     * @access public
     */
    public function staffInRole($uid, $roleid)
    {
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);
        if (!$inRole = $hStaffRole->staffInRole($uid, $roleid)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve amount of time spent by staff member
     * @param  int $uid user id
     * @return int $timeSpent
     * @access public
     */
    public function &getTimeSpent($uid = 0)
    {
        $hResponses = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        if (0 == !$uid) {
            $uid       = (int)$uid;
            $crit      = new \Criteria('uid', $uid);
            $responses = $hResponses->getObjects($crit);
        } else {
            $responses = $hResponses->getObjects();
        }
        $timeSpent = 0;
        foreach ($responses as $response) {
            $newTime   = $response->getVar('timeSpent');
            $timeSpent += $newTime;
        }

        return $timeSpent;
    }

    /**
     * @return array
     */
    public function &getByAllDepts()
    {
        $ret = $this->getObjects(new \Criteria('allDepartments', 1), true);

        return $ret;
    }

    /**
     * creates new staff member
     *
     * @access public
     * @param $uid
     * @param $email
     * @return bool|void
     */
    public function addStaff($uid, $email) //, $allDepts = 0
    {
        $notify = new Xhelp\NotificationService();
        $staff  = $this->create();
        $staff->setVar('uid', $uid);
        $staff->setVar('email', $email);
        $numNotify = $notify->getNumDeptNotifications();
        $staff->setVar('notify', (2 ** $numNotify) - 1);
        $staff->setVar('permTimestamp', time());

        return $this->insert($staff);
    }

    /**
     * checks to see if the user is a staff member
     *
     * @param  int $uid User ID to look for
     * @return bool TRUE if user is a staff member, false if not
     */
    public function isStaff($uid)
    {
        $count = $this->getCount(new \Criteria('uid', (int)$uid));

        return ($count > 0);
    }

    /**
     * @param $obj
     * @return string
     */
    public function _insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf(
            'INSERT INTO `%s` (id, uid, email, responseTime, numReviews, callsClosed, attachSig, rating, allDepartments, ticketsResponded, notify, permTimestamp) VALUES (%u, %u, %s, %u, %u, %u, %u, %u, %u, %u, %u, %u)',
            $this->_db->prefix($this->_dbtable),
            $id,
            $uid,
                       $this->_db->quoteString($email),
            $responseTime,
            $numReviews,
            $callsClosed,
            $attachSig,
            $rating,
            $allDepartments,
            $ticketsResponded,
            $notify,
            $permTimestamp
        );

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf(
            'UPDATE `%s` SET uid = %u, email = %s, responseTime = %u, numReviews = %u, callsClosed = %u, attachSig = %u, rating = %u, allDepartments = %u, ticketsResponded = %u, notify = %u, permTimestamp = %u WHERE id = %u',
            $this->_db->prefix($this->_dbtable),
            $uid,
                       $this->_db->quoteString($email),
            $responseTime,
            $numReviews,
            $callsClosed,
            $attachSig,
            $rating,
            $allDepartments,
            $ticketsResponded,
            $notify,
            $permTimestamp,
            $id
        );

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * delete a staff member from the database
     *
     * @param \XoopsObject $obj       reference to the {@link Xhelp\Staff}
     *                                obj to delete
     * @param  bool        $force
     * @return bool FALSE if failed.
     * @access  public
     */
    public function delete(\XoopsObject $obj, $force = false)
    {
        if (0 != strcasecmp($this->classname, get_class($obj))) {
            return false;
        }

        // Clear Department Membership
        $hMembership = new Xhelp\MembershipHandler($GLOBALS['xoopsDB']);
        if (!$hMembership->clearStaffMembership($obj->getVar('uid'))) {
            return false;
        }

        // Remove ticket lists
        $hTicketList = new Xhelp\TicketListHandler($GLOBALS['xoopsDB']);
        $crit        = new \Criteria('uid', $obj->getVar('uid'));
        if (!$hTicketList->deleteAll($crit)) {
            return false;
        }

        // Remove saved searches
        $hSavedSearch = new Xhelp\SavedSearchHandler($GLOBALS['xoopsDB']);
        if (!$hSavedSearch->deleteAll($crit)) {   // use existing crit object

            return false;
        }

        // Clear permission roles
        if (!$this->removeStaffRoles($obj->getVar('uid'))) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * Adjust the # of calls closed for the given user by the given offset
     *
     * @param  int $uid    User ID to modify
     * @param  int $offset Number of tickets to add to current call count (Negative for decrementing)
     * @return bool FALSE if query failed
     * @access  public
     */

    public function increaseCallsClosed($uid, $offset = 1)
    {
        if ($offset < 0) {
            $sql = sprintf('UPDATE `%s` SET callsClosed = callsClosed - %u WHERE uid = %u', $this->_db->prefix($this->_dbtable), abs($offset), $uid);
        } else {
            $sql = sprintf('UPDATE `%s` SET callsClosed = callsClosed + %u WHERE uid = %u', $this->_db->prefix($this->_dbtable), $offset, $uid);
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Adjust the responseTime for the specified staff member
     *
     * @param  int $uid          User ID to modify
     * @param  int $responseTime If $ticketCount is specified, the total # of response seconds, otherwise the number of seconds to add
     * @param  int $ticketCount  If = 0, increments 'responseTime' and 'ticketsResponded' otherwise, total # of tickets
     * @return bool FALSE if query failed
     * @access  public
     */
    public function updateResponseTime($uid, $responseTime, $ticketCount = 0)
    {
        if (0 == $ticketCount) {
            //Incrementing responseTime
            $sql = sprintf('UPDATE `%s` SET responseTime = responseTime + %u, ticketsResponded = ticketsResponded + 1 WHERE uid = %u', $this->_db->prefix($this->_dbtable), $responseTime, $uid);
        } else {
            //Setting responseTime, ticketsResponded
            $sql = sprintf('UPDATE `%s` SET responseTime = %u, ticketsResponded = %u WHERE uid = %u', $this->_db->prefix($this->_dbtable), $responseTime, $ticketCount, $uid);
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Adjust the rating for the specified staff member
     *
     * @param  int $uid        Staff ID to modify
     * @param  int $rating     If $numReviews is specified, the total # of rating points, otherwise the number of rating points to add
     * @param  int $numReviews If = 0, increments 'rating' and 'numReviews', otherwise total # of reviews
     * @return bool FALSE if query failed
     * @access public
     */
    public function updateRating($uid, $rating, $numReviews = 0)
    {
        if (0 == $numReviews) {
            //Add New Review
            $sql = sprintf('UPDATE `%s` SET rating = rating + %u, numReviews = numReviews + 1 WHERE uid = %u', $this->_db->prefix($this->_dbtable), $rating, $uid);
        } else {
            //Set rating, numReviews to supplied values
            $sql = sprintf('UPDATE `%s` SET rating = %u, numReviews = %u WHERE uid = %u', $this->_db->prefix($this->_dbtable), $rating, $numReviews, $uid);
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve array of all staff with permission for current task
     * @param      $task
     * @param int  $deptid
     * @param bool $id_as_key
     * @return array
     */
    public function getStaffByTask($task, $deptid = 0, $id_as_key = false)
    {
        $task = (int)$task;
        if (isset($deptid)) {
            $deptid = (int)$deptid;
        }

        // Get roles with $task value set
        $hRoles = new Xhelp\RoleHandler($GLOBALS['xoopsDB']);
        $roles  = $hRoles->getRolesByTask($task);
        $aRoles = [];
        foreach ($roles as $role) {
            $aRoles[$role->getVar('id')] = '';
        }

        // Get staff roles by dept
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);
        $crit       = new \CriteriaCompo(new \Criteria('deptid', $deptid));
        $crit->add(new \Criteria('roleid', '(' . implode(array_keys($aRoles), ',') . ')', 'IN'));
        unset($aRoles);

        $staffRoles = $hStaffRole->getObjects($crit);
        $aStaffID   = [];
        foreach ($staffRoles as $sRole) {
            $aStaffID[$sRole->getVar('uid')] = '';
        }

        // Get staff objects
        $crit   = new \Criteria('uid', '(' . implode(array_keys($aStaffID), ',') . ')', 'IN');
        $hStaff = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);

        return $hStaff->getObjects($crit, $id_as_key);
    }
}
