<?php
//$Id: membership.php,v 1.18 2005/10/12 19:43:25 eric_juden Exp $
class xhelpMembershipHandler {
    var $_db;
    var $_hStaff;
    var $_hDept;

    /**
     * Constructor
     *
     * @param object &$db {@link XoopsDatabase}
     * @access public
     */
    function xhelpMembershipHandler(&$db) {
        //Constructor
        $this->_db = $db;
        $this->_hStaff =& xhelpGetHandler('staff');
        $this->_hDept =& xhelpGetHandler('department');
        $this->_hTicket =& xhelpGetHandler('ticket');
    }

    /**
     * count objects matching a criteria
     *
     * @param  object $criteria {@link CriteriaElement} to match
     * @return int    count of objects
     * @access public
     */
    function getCount($criteria = null)
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s s INNER JOIN %s j on s.uid = j.uid', $this->_db->prefix('xhelp_staff'),$this->_db->prefix('xhelp_jstaffdept'));
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result =& $this->_db->query($sql)) {
            return 0;
        }
        list($count) = $this->_db->fetchRow($result);

        return $count;
    }
     
    /**
     * Get all departments a staff user is assigned to
     *
     * @param  int   $uid staff user id
     * @return array array of {@link xhelpDepartment} objects
     * @access public
     */
    function &membershipByStaff($uid, $id_as_key = false)
    {
        $uid = intval($uid);
        $sql = sprintf("SELECT d.* FROM %s d INNER JOIN %s j ON d.id = j.department WHERE j.uid = %u",$this->_db->prefix('xhelp_departments'),$this->_db->prefix('xhelp_jstaffdept'),$uid);

        $ret = $this->_db->query($sql);
        $arr = array();

        while ($temp = $this->_db->fetchArray($ret)) {
            $dept = $this->_hDept->create();
            $dept->assignVars($temp);
            if($id_as_key){
                $arr[$dept->getVar('id')] = $dept;
            } else {
                $arr[] = $dept;
            }
            unset($temp);
        }

        return $arr;

    }

    function &getVisibleDepartments($uid)
    {
        $uid = intval($uid);
        $xoopsModule =& xhelpGetModule();
        $module_id = $xoopsModule->getVar('mid');
        $hMember =& xoops_gethandler('member');
        $groups =& $hMember->getGroupsByUser($uid);
        $group_string = "(". implode(array_values($groups), ',') .")";

        $sql = sprintf("SELECT d.* FROM %s d INNER JOIN %s g ON d.id = g.gperm_itemid WHERE g.gperm_name = '%s' AND g.gperm_modid = '%s' AND g.gperm_groupid IN %s", $this->_db->prefix('xhelp_departments'), $this->_db->prefix('group_permission'), _XHELP_GROUP_PERM_DEPT, $module_id, $group_string);
        $ret = $this->_db->query($sql);
        $arr = array();

        while ($temp = $this->_db->fetchArray($ret)) {
            $dept = $this->_hDept->create();
            $dept->assignVars($temp);
            $arr[$dept->getVar('id')] = $dept;
            unset($temp);
        }

        return $arr;
    }

    function isStaffMember($uid, $deptid)
    {

        $sql = sprintf("SELECT COUNT(*) as MemberCount FROM %s WHERE uid = %u AND department = %u", $this->_db->prefix('xhelp_jstaffdept'), $uid, $deptid);
        $ret = $this->_db->query($sql);
        list($memberCount) = $this->_db->fetchRow($ret);

        return ($memberCount > 0);
    }

    /**
     * Get all staff members assigned to a department
     *
     * @param  int   $deptid department id
     * @return array array of {@link xhelpStaff} objects
     * @access public
     */
    function &membershipByDept($deptid, $limit=0, $start=0)
    {
        $limit = intval($limit);
        $start = intval($start);
        $a_depts = array();

        if (is_array($deptid)) {
            foreach($deptid as $dept) {
                if (is_object($dept)) {
                    $a_depts[] = $dept->getVar('id');
                } else {
                    $a_depts[] = intval($dept);
                }
            }
        } else {
            if (is_object($deptid)) {
                $a_depts[] = $deptid->getVar('id');
            } else {
                $a_depts[] = intval($deptid);
            }
        }
        if (count($a_depts) == 1) {
            $sql = sprintf('SELECT s.* FROM %s s INNER JOIN %s j on s.uid = j.uid WHERE j.department = %u', $this->_db->prefix('xhelp_staff'),$this->_db->prefix('xhelp_jstaffdept') ,$a_depts[0]);
        } else {
            $uids = $this->_uidsInDepts($a_depts);
            $sql = sprintf('SELECT s.* FROM %s s WHERE s.uid IN (%s)', $this->_db->prefix('xhelp_staff'),implode($uids, ','));
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = array();

        while($temp = $this->_db->fetchArray($ret)) {
            $staff = $this->_hStaff->create();
            $staff->assignVars($temp);
            $arr[$staff->getVar('uid')] = $staff;
            unset($temp);
        }

        return $arr;
    }

    function &xoopsUsersByDept($deptid, $limit=0, $start=0)
    {
        $limit = intval($limit);
        $start = intval($start);
        $a_depts = array();
        $hUser =& xoops_gethandler('user');

        if (is_array($deptid)) {
            foreach($deptid as $dept) {
                if (is_object($dept)) {
                    $a_depts[] = $dept->getVar('id');
                } else {
                    $a_depts[] = intval($dept);
                }
            }
        } else {
            if (is_object($deptid)) {
                $a_depts[] = $deptid->getVar('id');
            } else {
                $a_depts[] = intval($deptid);
            }
        }
        if (count($a_depts) == 1) {
            $sql = sprintf('SELECT u.* FROM %s u INNER JOIN %s j on u.uid = j.uid WHERE j.department = %u', $this->_db->prefix('users'),$this->_db->prefix('xhelp_jstaffdept') ,$a_depts[0]);
        } else {
            $uids = $this->_uidsInDepts($a_depts);
            $sql = sprintf('SELECT u.* FROM %s u WHERE u.uid IN (%s)', $this->_db->prefix('users'),implode($uids, ','));
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = array();

        while($temp = $this->_db->fetchArray($ret)) {
            $staff = $hUser->create();
            $staff->assignVars($temp);
            $arr[$staff->getVar('uid')] = $staff;
            unset($temp);
        }

        return $arr;
    }

    function inAllDepts($staffDepts)
    {
        $hDept =& xhelpGetHandler('department');
        $allDepts = $hDept->getCount();

        $numDepts = 0;
        foreach($staffDepts as $dept){
            $numDepts++;
        }

        if($allDepts != $numDepts){
            return false;
        }

        return true;
    }

    /**
     * Add the given staff member(s) to the given department
     *
     * @param  mixed $staff  single or array of uids or {@link xhelpStaff} objects
     * @param  int   $deptid Department ID
     * @return bool  True if successful, False if not
     * @access public
     */
    function addStaffToDept(&$staff, $deptid)
    {
        if (!is_array($staff)) {
            return $this->_addMembership($staff, $deptid);
        } else {
            foreach ($staff as $member) {
                $ret = $this->_addMembership($member, $deptid);
                if(!$ret){
                    exit;
                }
            }

            return $ret;
        }
    }

    /**
     * Add the given department(s) to the given user
     *
     * @param  mixed $dept single or array of department id's or {@link xhelpDepartment} objects
     * @param  int   $uid  User ID
     * @return bool  True if successful, False if not
     * @access public
     */
    function addDeptToStaff($dept, $uid)
    {
        if (!is_array($dept)) {
            return $this->_addMembership($uid, $dept);
        } else {
            foreach ($dept as $member) {
                $ret = $this->_addMembership($uid, $member);
                if(!$ret){
                    break;
                }
            }

            return $ret;
        }
    }

    /**
     * Remove the given staff member(s) to the given department
     *
     * @param  mixed $staff  single or array of uids or {@link xhelpStaff} objects
     * @param  int   $deptid Department ID
     * @return bool  True if successful, False if not
     * @access public
     */
    function  removeStaffFromDept(&$staff, $deptid)
    {
        if (!is_array($staff)) {
            return $this->_removeMembership($staff, $deptid);
        } else {
            foreach ($staff as $member) {
                $ret = $this->_removeMembership($member, $deptid);
                if(!$ret){
                    exit;
                }
            }

            return $ret;
        }
    }

    /**
     * Remove the given user from the given department(s)
     *
     * @param  mixed $dept single or array of department id's or {@link xhelpDepartment} objects
     * @param  int   $uid  User ID
     * @return bool  True if successful, False if not
     * @access public
     */
    function removeDeptFromStaff(&$dept, $uid)
    {
        if (!is_array($dept)) {
            return $this->_removeMembership($uid, $dept);
        } else {
            foreach ($dept as $member) {
                $ret = $this->_removeMembership($uid, $member);
                if(!$ret){
                    exit;
                }
            }

            return $ret;
        }
    }

    /**
     * Remove the specified user from all departments
     *
     * @param  int  $uid User ID
     * @return bool True if successful, False if not
     * @access public
     */

    function clearStaffMembership($uid)
    {
        $sql = sprintf("DELETE FROM %s WHERE uid=%u", $this->_db->prefix('xhelp_jstaffdept'), $uid);

        return $this->_db->query($sql);
    }

    /**
     * Remove all users from the specified department
     *
     * @param  int  $deptid Department ID
     * @return bool True if successful, False if not
     * @access public
     */
    function clearDeptMembership($deptid)
    {
        $sql = sprintf("DELETE FROM %s WHERE department=%u", $this->_db->prefix('xhelp_jstaffdept'), $deptid);

        return $this->_db->query($sql);
    }

    /**
     * Add a staff member to a department
     *
     * @param  mixed $staff uid or {@link xhelpStaff} object
     * @param  mixed $dept  department id or {@link xhelpDepartment} object
     * @return bool  True if successful, False if not
     * @access private
     */
    function _addMembership(&$staff, &$dept)
    {
        $deptid = 0;
        $staffid = 0;

        if (is_object($staff)) {
            $staffid = $staff->getVar('uid');
        } else {
            $staffid = intval($staff);
        }

        if (is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = intval($dept);
        }

        return $this->_addJoinerRecord($staffid, $deptid);
    }

    /**
     * Add a record to the joiner database table
     *
     * @param  int  $staffid user id
     * @param  int  $deptid  department id
     * @return bool True if successful, False if not
     * @access private
     */
    function _addJoinerRecord($staffid, $deptid)
    {
        $sql = sprintf('INSERT INTO %s (uid, department) VALUES(%u, %u)', $this->_db->prefix('xhelp_jstaffdept'), $staffid, $deptid);

        return $this->_db->query($sql);
    }

    /**
     * Remove a staff member from a department
     *
     * @param  mixed $staff uid or {@link xhelpStaff} object
     * @param  mixed $dept  department id or {@link xhelpDepartment} object
     * @return bool  True if successful, False if not
     * @access private
     */
    function _removeMembership(&$staff, &$dept)
    {
        $deptid = 0;
        $staffid = 0;

        if (is_object($staff)) {
            $staffid = $staff->getVar('uid');
        } else {
            $staffid = intval($staff);
        }

        if (is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = intval($dept);
        }

        return $this->_removeJoinerRecord($staffid, $deptid);
    }

    /**
     * Remove a record from the joiner db table
     *
     * @param  int  $staffid user id
     * @param  int  $deptid  department id
     * @return bool True if successful, False if not
     * @access private
     */
    function _removeJoinerRecord($staffid, $deptid)
    {
        $sql = sprintf('DELETE FROM %s WHERE uid=%u AND department=%u', $this->_db->prefix('xhelp_jstaffdept'), $staffid, $deptid);

        return $this->_db->queryF($sql);
    }

    function &_uidsInDepts($depts) {
        $sql = sprintf('SELECT j.uid FROM %s j WHERE j.department IN (%s) GROUP BY j.uid HAVING COUNT(*) = %u',
        $this->_db->prefix('xhelp_jstaffdept'), implode($depts, ','), count($depts));

        $ret = $this->_db->query($sql);
        $arr = array();

        while($temp = $this->_db->fetchArray($ret)) {
            $arr[] = $temp['uid'];
            unset($temp);
        }

        return $arr;
    }

}

/* Example Usages

1. Get all departments for a user
$uid = 14;
$hMembership &= xhelpGetHandler('membership');
$depts &= $hMembership->membershipByStaff($uid);

2. Get all staff members of a dept
$deptid = 5;
$hMembership &= xhelpGetHandler('membership');
$staff &= $hMembership->membershipByDept($deptid);

3. Add the current user to a department
$dept = 5;
$hMembership &= xhelpGetHandler('membership');
$bRet = $hMembership->addStaffToDept($xoopsUser, $dept);

or

$dept = 5;
$hMembership &= xhelpGetHandler('membership');
$bRet = $hMembership->addStaffToDept($xoopsUser->getVar('uid'), $dept);

4. Add an array of users to a department
$dept = 5;
$arr = array(5, 14, 18); //Array of uid's to add
$hMembership &= xhelpGetHandler('membership');
$bRet = $hMembership->addStaffToDept($arr, $dept);
*/;
