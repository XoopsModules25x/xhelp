<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * Class MembershipHandler
 */
class MembershipHandler
{
    public $_db;
    public $staffHandler;
    public $_hDept;

    /**
     * Constructor
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        //Constructor
        $this->_db      = $db;
        $this->staffHandler  = new StaffHandler($GLOBALS['xoopsDB']);
        $this->_hDept   = new DepartmentHandler($GLOBALS['xoopsDB']);
        $this->_hTicket = new TicketHandler($GLOBALS['xoopsDB']);
    }

    /**
     * count objects matching a criteria
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement} to match
     * @return int    count of objects
     */
    public function getCount($criteria = null): int
    {
        $sql = \sprintf('SELECT COUNT(*) FROM `%s` s INNER JOIN %s j ON s.uid = j.uid', $this->_db->prefix('xhelp_staff'), $this->_db->prefix('xhelp_jstaffdept'));
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return 0;
        }
        [$count] = $this->_db->fetchRow($result);

        return (int)$count;
    }

    /**
     * Get all departments a staff user is assigned to
     *
     * @param int  $uid staff user id
     * @param bool $id_as_key
     * @return array array of <a href='psi_element://Department'>Department</a> objects
     *                  objects
     */
    public function &membershipByStaff($uid, $id_as_key = false): array
    {
        $uid = (int)$uid;
        $sql = \sprintf('SELECT d.* FROM `%s` d INNER JOIN %s j ON d.id = j.department WHERE j.uid = %u', $this->_db->prefix('xhelp_departments'), $this->_db->prefix('xhelp_jstaffdept'), $uid);

        $ret = $this->_db->query($sql);
        $arr = [];

        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $dept = $this->_hDept->create();
            $dept->assignVars($temp);
            if ($id_as_key) {
                $arr[$dept->getVar('id')] = $dept;
            } else {
                $arr[] = $dept;
            }
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param $uid
     * @return array
     */
    public function &getVisibleDepartments($uid): array
    {
        $uid           = (int)$uid;
        $xoopsModule   = Utility::getModule();
        $module_id     = $xoopsModule->getVar('mid');
        $memberHandler = \xoops_getHandler('member');
        $groups        = $memberHandler->getGroupsByUser($uid);
        $group_string  = '(' . \implode(',', \array_values($groups)) . ')';

        $sql = \sprintf("SELECT d.* FROM `%s` d INNER JOIN %s g ON d.id = g.gperm_itemid WHERE g.gperm_name = '%s' AND g.gperm_modid = '%s' AND g.gperm_groupid IN %s", $this->_db->prefix('xhelp_departments'), $this->_db->prefix('group_permission'), \_XHELP_GROUP_PERM_DEPT, $module_id, $group_string);
        $ret = $this->_db->query($sql);
        $arr = [];

        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $dept = $this->_hDept->create();
            $dept->assignVars($temp);
            $arr[$dept->getVar('id')] = $dept;
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param $uid
     * @param $deptid
     * @return bool
     */
    public function isStaffMember($uid, $deptid): bool
    {
        $sql = \sprintf('SELECT COUNT(*) AS MemberCount FROM `%s` WHERE uid = %u AND department = %u', $this->_db->prefix('xhelp_jstaffdept'), $uid, $deptid);
        $ret = $this->_db->query($sql);
        [$memberCount] = $this->_db->fetchRow($ret);

        return ($memberCount > 0);
    }

    /**
     * Get all staff members assigned to a department
     *
     * @param int|array $deptid department id
     * @param int       $limit
     * @param int       $start
     * @return array array of <a href='psi_element://Staff'>Staff</a> objects
     *                          objects
     */
    public function &membershipByDept($deptid, $limit = 0, $start = 0): array
    {
        $limit   = (int)$limit;
        $start   = (int)$start;
        $a_depts = [];

        if (\is_array($deptid)) {
            foreach ($deptid as $dept) {
                if (\is_object($dept)) {
                    $a_depts[] = $dept->getVar('id');
                } else {
                    $a_depts[] = (int)$dept;
                }
            }
        } else {
            if (\is_object($deptid)) {
                $a_depts[] = $deptid->getVar('id');
            } else {
                $a_depts[] = (int)$deptid;
            }
        }
        if (1 == \count($a_depts)) {
            $sql = \sprintf('SELECT s.* FROM `%s` s INNER JOIN %s j ON s.uid = j.uid WHERE j.department = %u', $this->_db->prefix('xhelp_staff'), $this->_db->prefix('xhelp_jstaffdept'), $a_depts[0]);
        } else {
            $uids = $this->_uidsInDepts($a_depts);
            $sql  = \sprintf('SELECT s.* FROM `%s` s WHERE s.uid IN (%s)', $this->_db->prefix('xhelp_staff'), \implode(',', $uids));
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = [];

        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $staff = $this->staffHandler->create();
            $staff->assignVars($temp);
            $arr[$staff->getVar('uid')] = $staff;
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param     $deptid
     * @param int $limit
     * @param int $start
     * @return array
     */
    public function &xoopsUsersByDept($deptid, $limit = 0, $start = 0): array
    {
        $limit       = (int)$limit;
        $start       = (int)$start;
        $a_depts     = [];
        $userHandler = \xoops_getHandler('user');

        if (\is_array($deptid)) {
            foreach ($deptid as $dept) {
                if (\is_object($dept)) {
                    $a_depts[] = $dept->getVar('id');
                } else {
                    $a_depts[] = (int)$dept;
                }
            }
        } else {
            if (\is_object($deptid)) {
                $a_depts[] = $deptid->getVar('id');
            } else {
                $a_depts[] = (int)$deptid;
            }
        }
        if (1 == \count($a_depts)) {
            $sql = \sprintf('SELECT u.* FROM `%s` u INNER JOIN %s j ON u.uid = j.uid WHERE j.department = %u', $this->_db->prefix('users'), $this->_db->prefix('xhelp_jstaffdept'), $a_depts[0]);
        } else {
            $uids = $this->_uidsInDepts($a_depts);
            $sql  = \sprintf('SELECT u.* FROM `%s` u WHERE u.uid IN (%s)', $this->_db->prefix('users'), \implode(',', $uids));
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = [];

        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $staff = $userHandler->create();
            $staff->assignVars($temp);
            $arr[$staff->getVar('uid')] = $staff;
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param $staffDepts
     * @return bool
     */
    public function inAllDepts($staffDepts): bool
    {
        $departmentHandler = new DepartmentHandler($GLOBALS['xoopsDB']);
        $allDepts          = $departmentHandler->getCount();

        $numDepts = 0;
        foreach ($staffDepts as $dept) {
            ++$numDepts;
        }

        if ($allDepts != $numDepts) {
            return false;
        }

        return true;
    }

    /**
     * Add the given staff member(s) to the given department
     *
     * @param mixed $staff  single or array of uids or {@link Staff} objects
     * @param int   $deptid Department ID
     * @return bool  True if successful, False if not
     */
    public function addStaffToDept($staff, $deptid): bool
    {
        if (!\is_array($staff)) {
            return $this->_addMembership($staff, $deptid);
        }

        foreach ($staff as $member) {
            $ret = $this->_addMembership($member, $deptid);
            if (!$ret) {
                exit;
            }
        }

        return $ret;
    }

    /**
     * Add the given department(s) to the given user
     *
     * @param mixed $dept single or array of department id's or {@link Department} objects
     * @param int   $uid  User ID
     * @return bool  True if successful, False if not
     */
    public function addDeptToStaff($dept, $uid): bool
    {
        if (!\is_array($dept)) {
            return $this->_addMembership($uid, $dept);
        }

        foreach ($dept as $member) {
            $ret = $this->_addMembership($uid, $member);
            if (!$ret) {
                break;
            }
        }

        return $ret;
    }

    /**
     * Remove the given staff member(s) to the given department
     *
     * @param mixed $staff  single or array of uids or {@link Staff} objects
     * @param int   $deptid Department ID
     * @return bool  True if successful, False if not
     */
    public function removeStaffFromDept($staff, $deptid): bool
    {
        if (!\is_array($staff)) {
            return $this->_removeMembership($staff, $deptid);
        }

        foreach ($staff as $member) {
            $ret = $this->_removeMembership($member, $deptid);
            if (!$ret) {
                exit;
            }
        }

        return $ret;
    }

    /**
     * Remove the given user from the given department(s)
     *
     * @param mixed $dept single or array of department id's or {@link Department} objects
     * @param int   $uid  User ID
     * @return bool  True if successful, False if not
     */
    public function removeDeptFromStaff($dept, $uid): bool
    {
        if (!\is_array($dept)) {
            return $this->_removeMembership($uid, $dept);
        }

        foreach ($dept as $member) {
            $ret = $this->_removeMembership($uid, $member);
            if (!$ret) {
                exit;
            }
        }

        return $ret;
    }

    /**
     * Remove the specified user from all departments
     *
     * @param int $uid User ID
     * @return bool True if successful, False if not
     */
    public function clearStaffMembership($uid): bool
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE uid=%u', $this->_db->prefix('xhelp_jstaffdept'), $uid);

        return $this->_db->query($sql);
    }

    /**
     * Remove all users from the specified department
     *
     * @param int $deptid Department ID
     * @return bool True if successful, False if not
     */
    public function clearDeptMembership($deptid): bool
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE department=%u', $this->_db->prefix('xhelp_jstaffdept'), $deptid);

        return $this->_db->query($sql);
    }

    /**
     * Add a staff member to a department
     *
     * @param mixed $staff uid or {@link Staff} object
     * @param mixed $dept  department id or {@link Department} object
     * @return bool  True if successful, False if not
     */
    public function _addMembership($staff, $dept): bool
    {
        $deptid  = 0;
        $staffid = 0;

        if (\is_object($staff)) {
            $staffid = $staff->getVar('uid');
        } else {
            $staffid = (int)$staff;
        }

        if (\is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = (int)$dept;
        }

        return $this->_addJoinerRecord($staffid, $deptid);
    }

    /**
     * Add a record to the joiner database table
     *
     * @param int $staffid user id
     * @param int $deptid  department id
     * @return bool True if successful, False if not
     */
    public function _addJoinerRecord($staffid, $deptid): bool
    {
        $sql = \sprintf('INSERT INTO `%s` (uid, department) VALUES(%u, %u)', $this->_db->prefix('xhelp_jstaffdept'), $staffid, $deptid);

        return $this->_db->query($sql);
    }

    /**
     * Remove a staff member from a department
     *
     * @param mixed $staff uid or {@link Staff} object
     * @param mixed $dept  department id or {@link Department} object
     * @return bool  True if successful, False if not
     */
    public function _removeMembership($staff, $dept): bool
    {
        $deptid  = 0;
        $staffid = 0;

        if (\is_object($staff)) {
            $staffid = $staff->getVar('uid');
        } else {
            $staffid = (int)$staff;
        }

        if (\is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = (int)$dept;
        }

        return $this->_removeJoinerRecord($staffid, $deptid);
    }

    /**
     * Remove a record from the joiner db table
     *
     * @param int $staffid user id
     * @param int $deptid  department id
     * @return bool True if successful, False if not
     */
    public function _removeJoinerRecord($staffid, $deptid): bool
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE uid=%u AND department=%u', $this->_db->prefix('xhelp_jstaffdept'), $staffid, $deptid);

        return $this->_db->queryF($sql);
    }

    /**
     * @param $depts
     * @return array
     */
    public function &_uidsInDepts($depts): array
    {
        $sql = \sprintf('SELECT j.uid FROM `%s` j WHERE j.department IN (%s) GROUP BY j.uid HAVING COUNT(*) = %u', $this->_db->prefix('xhelp_jstaffdept'), \implode(',', $depts), \count($depts));

        $ret = $this->_db->query($sql);
        $arr = [];

        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $arr[] = $temp['uid'];
            unset($temp);
        }

        return $arr;
    }
}

/* Example Usages

1. Get all departments for a user
$uid = 14;
$membershipHandler &= new MembershipHandler($GLOBALS['xoopsDB']);
$depts &= $membershipHandler->membershipByStaff($uid);

2. Get all staff members of a dept
$deptid = 5;
$membershipHandler &= new MembershipHandler($GLOBALS['xoopsDB']);
$staff &= $membershipHandler->membershipByDept($deptid);

3. Add the current user to a department
$dept = 5;
$membershipHandler &= new MembershipHandler($GLOBALS['xoopsDB']);
$bRet = $membershipHandler->addStaffToDept($xoopsUser, $dept);

or

$dept = 5;
$membershipHandler &= new MembershipHandler($GLOBALS['xoopsDB']);
$bRet = $membershipHandler->addStaffToDept($xoopsUser->getVar('uid'), $dept);

4. Add an array of users to a department
$dept = 5;
$arr = array(5, 14, 18); //Array of uid's to add
$membershipHandler &= new MembershipHandler($GLOBALS['xoopsDB']);
$bRet = $membershipHandler->addStaffToDept($arr, $dept);
*/
