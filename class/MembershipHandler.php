<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * Class MembershipHandler
 */
class MembershipHandler
{
    private $db;
    private $staffHandler;
    private $departmentHandler;
    private $ticketHandler;

    /**
     * Constructor
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        //Constructor
        $this->db = $db;
        $helper   = Helper::getInstance();
        /** @var \XoopsModules\Xhelp\StaffHandler $this- >staffHandler */
        $this->staffHandler = $helper->getHandler('Staff');
        /** @var \XoopsModules\Xhelp\DepartmentHandler $this- >departmentHandler */
        $this->departmentHandler = $helper->getHandler('Department');
        /** @var \XoopsModules\Xhelp\TicketHandler $this- >ticketHandler */
        $this->ticketHandler = $helper->getHandler('Ticket');
    }

    /**
     * count objects matching a criteria
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement} to match
     * @return int    count of objects
     */
    public function getCount($criteria = null): int
    {
        $sql = \sprintf('SELECT COUNT(*) FROM `%s` s INNER JOIN %s j ON s.uid = j.uid', $this->db->prefix('xhelp_staff'), $this->db->prefix('xhelp_jstaffdept'));
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return 0;
        }
        [$count] = $this->db->fetchRow($result);

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
    public function &membershipByStaff(int $uid, bool $id_as_key = false): array
    {
        $uid = $uid;
        $sql = \sprintf('SELECT d.* FROM `%s` d INNER JOIN %s j ON d.id = j.department WHERE j.uid = %u', $this->db->prefix('xhelp_departments'), $this->db->prefix('xhelp_jstaffdept'), $uid);

        $ret = $this->db->query($sql);
        $arr = [];

        while (false !== ($temp = $this->db->fetchArray($ret))) {
            $dept = $this->departmentHandler->create();
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
     * @param int $uid
     * @return array
     */
    public function &getVisibleDepartments(int $uid): array
    {
        $uid         = $uid;
        $xoopsModule = Utility::getModule();
        $module_id   = $xoopsModule->getVar('mid');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        $groups        = $memberHandler->getGroupsByUser($uid);
        $group_string  = '(' . \implode(',', \array_values($groups)) . ')';

        $sql = \sprintf("SELECT d.* FROM `%s` d INNER JOIN %s g ON d.id = g.gperm_itemid WHERE g.gperm_name = '%s' AND g.gperm_modid = '%s' AND g.gperm_groupid IN %s", $this->db->prefix('xhelp_departments'), $this->db->prefix('group_permission'), \_XHELP_GROUP_PERM_DEPT, $module_id, $group_string);
        $ret = $this->db->query($sql);
        $arr = [];

        while (false !== ($temp = $this->db->fetchArray($ret))) {
            $dept = $this->departmentHandler->create();
            $dept->assignVars($temp);
            $arr[$dept->getVar('id')] = $dept;
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param int $uid
     * @param int $deptid
     * @return bool
     */
    public function isStaffMember(int $uid, int $deptid): bool
    {
        $sql = \sprintf('SELECT COUNT(*) AS MemberCount FROM `%s` WHERE uid = %u AND department = %u', $this->db->prefix('xhelp_jstaffdept'), $uid, $deptid);
        $ret = $this->db->query($sql);
        [$memberCount] = $this->db->fetchRow($ret);

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
    public function &membershipByDept($deptid, int $limit = 0, int $start = 0): array
    {
        $limit   = $limit;
        $start   = $start;
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
            $sql = \sprintf('SELECT s.* FROM `%s` s INNER JOIN %s j ON s.uid = j.uid WHERE j.department = %u', $this->db->prefix('xhelp_staff'), $this->db->prefix('xhelp_jstaffdept'), $a_depts[0]);
        } else {
            $uids = $this->uidsInDepts($a_depts);
            $sql  = \sprintf('SELECT s.* FROM `%s` s WHERE s.uid IN (%s)', $this->db->prefix('xhelp_staff'), \implode(',', $uids));
        }

        $ret = $this->db->query($sql, $limit, $start);
        $arr = [];

        while (false !== ($temp = $this->db->fetchArray($ret))) {
            $staff = $this->staffHandler->create();
            $staff->assignVars($temp);
            $arr[$staff->getVar('uid')] = $staff;
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param array|int $deptid
     * @param int       $limit
     * @param int       $start
     * @return array
     */
    public function &xoopsUsersByDept($deptid, int $limit = 0, int $start = 0): array
    {
        $limit       = $limit;
        $start       = $start;
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
            $sql = \sprintf('SELECT u.* FROM `%s` u INNER JOIN %s j ON u.uid = j.uid WHERE j.department = %u', $this->db->prefix('users'), $this->db->prefix('xhelp_jstaffdept'), $a_depts[0]);
        } else {
            $uids = $this->uidsInDepts($a_depts);
            $sql  = \sprintf('SELECT u.* FROM `%s` u WHERE u.uid IN (%s)', $this->db->prefix('users'), \implode(',', $uids));
        }

        $ret = $this->db->query($sql, $limit, $start);
        $arr = [];

        while (false !== ($temp = $this->db->fetchArray($ret))) {
            $staff = $userHandler->create();
            $staff->assignVars($temp);
            $arr[$staff->getVar('uid')] = $staff;
            unset($temp);
        }

        return $arr;
    }

    /**
     * @param array $staffDepts
     * @return bool
     */
    public function inAllDepts(array $staffDepts): bool
    {
        $helper = Helper::getInstance();
        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');
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
    public function addStaffToDept($staff, int $deptid): bool
    {
        $ret = false;
        if (!\is_array($staff)) {
            return $this->addMembership($staff, $deptid);
        }

        foreach ($staff as $member) {
            $ret = $this->addMembership($member, $deptid);
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
    public function addDeptToStaff($dept, int $uid): bool
    {
        $ret = false;
        if (!\is_array($dept)) {
            return $this->addMembership($uid, $dept);
        }

        foreach ($dept as $member) {
            $ret = $this->addMembership($uid, $member);
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
    public function removeStaffFromDept($staff, int $deptid): bool
    {
        $ret = false;
        if (!\is_array($staff)) {
            return $this->removeMembership($staff, $deptid);
        }

        foreach ($staff as $member) {
            $ret = $this->removeMembership($member, $deptid);
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
    public function removeDeptFromStaff($dept, int $uid): bool
    {
        $ret = false;
        if (!\is_array($dept)) {
            return $this->removeMembership($uid, $dept);
        }

        foreach ($dept as $member) {
            $ret = $this->removeMembership($uid, $member);
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
    public function clearStaffMembership(int $uid): bool
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE uid=%u', $this->db->prefix('xhelp_jstaffdept'), $uid);

        return $this->db->query($sql);
    }

    /**
     * Remove all users from the specified department
     *
     * @param int $deptid Department ID
     * @return bool True if successful, False if not
     */
    public function clearDeptMembership(int $deptid): bool
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE department=%u', $this->db->prefix('xhelp_jstaffdept'), $deptid);

        return $this->db->query($sql);
    }

    /**
     * Add a staff member to a department
     *
     * @param mixed $staff uid or {@link Staff} object
     * @param mixed $dept  department id or {@link Department} object
     * @return bool  True if successful, False if not
     */
    public function addMembership($staff, $dept): bool
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

        return $this->addJoinerRecord($staffid, $deptid);
    }

    /**
     * Add a record to the joiner database table
     *
     * @param int $staffid user id
     * @param int $deptid  department id
     * @return bool True if successful, False if not
     */
    private function addJoinerRecord(int $staffid, int $deptid): bool
    {
        $sql = \sprintf('INSERT INTO `%s` (uid, department) VALUES(%u, %u)', $this->db->prefix('xhelp_jstaffdept'), $staffid, $deptid);

        return $this->db->query($sql);
    }

    /**
     * Remove a staff member from a department
     *
     * @param mixed $staff uid or {@link Staff} object
     * @param mixed $dept  department id or {@link Department} object
     * @return bool  True if successful, False if not
     */
    private function removeMembership($staff, $dept): bool
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

        return $this->removeJoinerRecord($staffid, $deptid);
    }

    /**
     * Remove a record from the joiner db table
     *
     * @param int $staffid user id
     * @param int $deptid  department id
     * @return bool True if successful, False if not
     */
    private function removeJoinerRecord(int $staffid, int $deptid): bool
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE uid=%u AND department=%u', $this->db->prefix('xhelp_jstaffdept'), $staffid, $deptid);

        return $this->db->queryF($sql);
    }

    /**
     * @param array $depts
     * @return array
     */
    private function &uidsInDepts(array $depts): array
    {
        $sql = \sprintf('SELECT j.uid FROM `%s` j WHERE j.department IN (%s) GROUP BY j.uid HAVING COUNT(*) = %u', $this->db->prefix('xhelp_jstaffdept'), \implode(',', $depts), \count($depts));

        $ret = $this->db->query($sql);
        $arr = [];

        while (false !== ($temp = $this->db->fetchArray($ret))) {
            $arr[] = $temp['uid'];
            unset($temp);
        }

        return $arr;
    }
}

/* Example Usages

1. Get all departments for a user
$uid = 14;
$membershipHandler &= $helper->getHandler('Membership');
$depts &= $membershipHandler->membershipByStaff($uid);

2. Get all staff members of a dept
$deptid = 5;
$membershipHandler &= $helper->getHandler('Membership');
$staff &= $membershipHandler->membershipByDept($deptid);

3. Add the current user to a department
$dept = 5;
$membershipHandler &= $helper->getHandler('Membership');
$bRet = $membershipHandler->addStaffToDept($xoopsUser, $dept);

or

$dept = 5;
$membershipHandler &= $helper->getHandler('Membership');
$bRet = $membershipHandler->addStaffToDept($xoopsUser->getVar('uid'), $dept);

4. Add an array of users to a department
$dept = 5;
$arr = array(5, 14, 18); //Array of uid's to add
$membershipHandler &= $helper->getHandler('Membership');
$bRet = $membershipHandler->addStaffToDept($arr, $dept);
*/
