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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
// require_once XHELP_CLASS_PATH . '/NotificationService.php';

/**
 * Staff class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */

// require_once XHELP_CLASS_PATH . '/session.php';

/**
 * StaffHandler class
 *
 * Staff Handler for Staff class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class StaffHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = Staff::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $dbtable = 'xhelp_staff';

    /**
     * Constructor
     *
     * @param null|\XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        $this->helper = Helper::getInstance();
        parent::init($db);
    }

    /**
     * retrieve a staff object from the database
     * @param int $uid user id
     * @return bool|Staff
     */
    public function &getByUid(int $uid)
    {
        $ret = false;
        $uid = $uid;
        if ($uid > 0) {
            $sql = $this->selectQuery(new \Criteria('uid', (string)$uid));
            if (!$result = $this->db->query($sql)) {
                return $ret;
            }
            $arr = $this->db->fetchArray($result);
            if ($arr) {
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
     * @return bool true if success, FALSE if failure
     */
    public function addStaffRole(int $uid, int $roleid, int $deptid): bool
    {
        $staffRoleHandler = $this->helper->getHandler('StaffRole');
        $role             = $staffRoleHandler->create();
        $role->setVar('uid', $uid);
        $role->setVar('roleid', $roleid);
        $role->setVar('deptid', $deptid);
        if (!$staffRoleHandler->insert($role)) {
            return false;
        }

        return true;
    }

    /**
     * Retrive all roles of the current staff member
     *
     * @param int  $uid
     * @param bool $id_as_key
     * @return array|bool StaffRoles or FALSE if failure
     */
    public function getRoles(int $uid, bool $id_as_key = false)
    {
        $uid = $uid;
        /** @var \XoopsModules\Xhelp\StaffRoleHandler $staffRoleHandler */
        $staffRoleHandler = $this->helper->getHandler('StaffRole');

        if (!$roles = $staffRoleHandler->getObjectsByStaff($uid, $id_as_key)) {
            return false;
        }

        return $roles;
    }

    /**
     * @return bool
     */
    public function clearRoles(): bool
    {
        $session = Session::getInstance();

        $myRoles = $session->get('xhelp_hasRights');
        if ($myRoles) {
            $session->del('xhelp_hasRights');

            return true;
        }

        return false;
    }

    /**
     * Retrieve all of the roles of current department for staff member
     *
     * @param int  $uid
     * @param int  $deptid
     * @param bool $id_as_key
     * @return array|bool array of StaffRoles or FALSE if failure
     */
    public function getRolesByDept(int $uid, int $deptid, bool $id_as_key = false)
    {
        $ret              = false;
        $uid              = $uid;
        $deptid           = $deptid;
        $staffRoleHandler = $this->helper->getHandler('StaffRole');

        $criteria = new \CriteriaCompo(new \Criteria('uid', (string)$uid));
        $criteria->add(new \Criteria('deptid', (string)$deptid));

        if (!$roles = $staffRoleHandler->getObjects($criteria, $id_as_key)) {
            return $ret;
        }

        return $roles;
    }

    /**
     * Remove user from a role
     *
     * @param int $uid user id
     * @return true if success, FALSE if failure
     * @internal param int $roleid role id
     * @internal param int $deptid department id
     */
    public function removeStaffRoles(int $uid): bool
    {
        $staffRoleHandler = $this->helper->getHandler('StaffRole');
        $criteria         = new \Criteria('uid', (string)$uid);

        return $staffRoleHandler->deleteAll($criteria);
    }

    /**
     * Check if a user is in a particular role
     *
     * @param int $uid    user id
     * @param int $roleid role id
     *
     * @return bool true on success, FALSE on failure
     */
    public function staffInRole(int $uid, int $roleid): bool
    {
        $staffRoleHandler = $this->helper->getHandler('StaffRole');
        if (!$inRole = $staffRoleHandler->staffInRole($uid, $roleid)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve amount of time spent by staff member
     * @param int $uid user id
     * @return int
     */
    public function &getTimeSpent(int $uid = 0): int
    {
        $responseHandler = $this->helper->getHandler('Response');
        if (0 == !$uid) {
            $uid       = $uid;
            $criteria  = new \Criteria('uid', (string)$uid);
            $responses = $responseHandler->getObjects($criteria);
        } else {
            $responses = $responseHandler->getObjects();
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
    public function &getByAllDepts(): array
    {
        $ret = $this->getObjects(new \Criteria('allDepartments', '1'), true);

        return $ret;
    }

    /**
     * creates new staff member
     *
     * @param int    $uid
     * @param string $email
     * @return bool
     */
    public function addStaff(int $uid, string $email) //, $allDepts = 0
    : bool
    {
        $notify = new NotificationService();
        /** @var \XoopsModules\Xhelp\Staff $staff */
        $staff = $this->create();
        $staff->setVar('uid', $uid);
        $staff->setVar('email', $email);
        $numNotify = $notify->getNumDeptNotifications();
        $staff->setVar('notify', (2 ** $numNotify) - 1);
        $staff->setVar('permTimestamp', \time());

        return $this->insert($staff);
    }

    /**
     * checks to see if the user is a staff member
     *
     * @param int $uid User ID to look for
     * @return bool TRUE if user is a staff member, false if not
     */
    public function isStaff(int $uid): bool
    {
        $count = $this->getCount(new \Criteria('uid', (string)$uid));

        return ($count > 0);
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'INSERT INTO `%s` (id, uid, email, responseTime, numReviews, callsClosed, attachSig, rating, allDepartments, ticketsResponded, notify, permTimestamp) VALUES (%u, %u, %s, %u, %u, %u, %u, %u, %u, %u, %u, %u)',
            $this->db->prefix($this->dbtable),
            $id,
            $uid,
            $this->db->quoteString($email),
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
     * @param \XoopsObject $object
     * @return string
     */
    public function updateQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'UPDATE `%s` SET uid = %u, email = %s, responseTime = %u, numReviews = %u, callsClosed = %u, attachSig = %u, rating = %u, allDepartments = %u, ticketsResponded = %u, notify = %u, permTimestamp = %u WHERE id = %u',
            $this->db->prefix($this->dbtable),
            $uid,
            $this->db->quoteString($email),
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
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar('id'));

        return $sql;
    }

    /**
     * delete a staff member from the database
     *
     * @param \XoopsObject $object    reference to the {@link Staff}
     *                                obj to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $object, $force = false): bool
    {
        if (0 != \strcasecmp($this->classname, \get_class($object))) {
            return false;
        }

        // Clear Department Membership
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $this->helper->getHandler('Membership');
        if (!$membershipHandler->clearStaffMembership($object->getVar('uid'))) {
            return false;
        }

        // Remove ticket lists
        $ticketListHandler = $this->helper->getHandler('TicketList');
        $criteria          = new \Criteria('uid', $object->getVar('uid'));
        if (!$ticketListHandler->deleteAll($criteria)) {
            return false;
        }

        // Remove saved searches
        $savedSearchHandler = $this->helper->getHandler('SavedSearch');
        if (!$savedSearchHandler->deleteAll($criteria)) {   // use existing crit object
            return false;
        }

        // Clear permission roles
        if (!$this->removeStaffRoles($object->getVar('uid'))) {
            return false;
        }

        $ret = parent::delete($object, $force);

        return $ret;
    }

    /**
     * Adjust the # of calls closed for the given user by the given offset
     *
     * @param int $uid    User ID to modify
     * @param int $offset Number of tickets to add to current call count (Negative for decrementing)
     * @return bool FALSE if query failed
     */
    public function increaseCallsClosed(int $uid, int $offset = 1): bool
    {
        if ($offset < 0) {
            $sql = \sprintf('UPDATE `%s` SET callsClosed = callsClosed - %u WHERE uid = %u', $this->db->prefix($this->dbtable), \abs($offset), $uid);
        } else {
            $sql = \sprintf('UPDATE `%s` SET callsClosed = callsClosed + %u WHERE uid = %u', $this->db->prefix($this->dbtable), $offset, $uid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Adjust the responseTime for the specified staff member
     *
     * @param int $uid          User ID to modify
     * @param int $responseTime If $ticketCount is specified, the total # of response seconds, otherwise the number of seconds to add
     * @param int $ticketCount  If = 0, increments 'responseTime' and 'ticketsResponded' otherwise, total # of tickets
     * @return bool FALSE if query failed
     */
    public function updateResponseTime(int $uid, int $responseTime, int $ticketCount = 0): bool
    {
        if (0 == $ticketCount) {
            //Incrementing responseTime
            $sql = \sprintf('UPDATE `%s` SET responseTime = responseTime + %u, ticketsResponded = ticketsResponded + 1 WHERE uid = %u', $this->db->prefix($this->dbtable), $responseTime, $uid);
        } else {
            //Setting responseTime, ticketsResponded
            $sql = \sprintf('UPDATE `%s` SET responseTime = %u, ticketsResponded = %u WHERE uid = %u', $this->db->prefix($this->dbtable), $responseTime, $ticketCount, $uid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Adjust the rating for the specified staff member
     *
     * @param int $uid        Staff ID to modify
     * @param int $rating     If $numReviews is specified, the total # of rating points, otherwise the number of rating points to add
     * @param int $numReviews If = 0, increments 'rating' and 'numReviews', otherwise total # of reviews
     * @return bool FALSE if query failed
     */
    public function updateRating(int $uid, int $rating, int $numReviews = 0): bool
    {
        if (0 == $numReviews) {
            //Add New Review
            $sql = \sprintf('UPDATE `%s` SET rating = rating + %u, numReviews = numReviews + 1 WHERE uid = %u', $this->db->prefix($this->dbtable), $rating, $uid);
        } else {
            //Set rating, numReviews to supplied values
            $sql = \sprintf('UPDATE `%s` SET rating = %u, numReviews = %u WHERE uid = %u', $this->db->prefix($this->dbtable), $rating, $numReviews, $uid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve array of all staff with permission for current task
     * @param int  $task
     * @param int  $deptid
     * @param bool $id_as_key
     * @return array
     */
    public function getStaffByTask(int $task, int $deptid = 0, bool $id_as_key = false): array
    {
        $task = $task;
        if (null !== $deptid) {
            $deptid = $deptid;
        }

        // Get roles with $task value set
        $roleHandler = $this->helper->getHandler('Role');
        $roles       = $roleHandler->getRolesByTask($task);
        $aRoles      = [];
        foreach ($roles as $role) {
            $aRoles[$role->getVar('id')] = '';
        }

        // Get staff roles by dept
        $staffRoleHandler = $this->helper->getHandler('StaffRole');
        $criteria         = new \CriteriaCompo(new \Criteria('deptid', (string)$deptid));
        $criteria->add(new \Criteria('roleid', '(' . \implode(',', \array_keys($aRoles)) . ')', 'IN'));
        unset($aRoles);

        $staffRoles = $staffRoleHandler->getObjects($criteria);
        $aStaffID   = [];
        foreach ($staffRoles as $sRole) {
            $aStaffID[$sRole->getVar('uid')] = '';
        }

        // Get staff objects
        $criteria     = new \Criteria('uid', '(' . \implode(',', \array_keys($aStaffID)) . ')', 'IN');
        $staffHandler = $this->helper->getHandler('Staff');

        return $staffHandler->getObjects($criteria, $id_as_key);
    }
}
