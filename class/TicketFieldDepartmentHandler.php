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

/**
 * class TicketFieldDepartmentHandler
 */
class TicketFieldDepartmentHandler
{
    public $_db;
    public $_hField;
    public $_hDept;

    /**
     * Constructor
     *
     * @param \XoopsDatabase $db
     * @access public
     */
    public function __construct(\XoopsDatabase $db)
    {
        $this->_db     = $db;
        $this->_hField = new Xhelp\TicketFieldHandler($GLOBALS['xoopsDB']);
        $this->_hDept  = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
    }

    /**
     * Get every department a field is "in"
     *
     * @param  int  $field     Field ID
     * @param  bool $id_as_key Should object ID be used as array key?
     * @return array array of {@Link Xhelp\Department} objects
     * @access public
     */
    public function departmentsByField($field, $id_as_key = false)
    {
        $field = (int)$field;
        $sql   = sprintf('SELECT d.* FROM `%s` d INNER JOIN %s j ON d.id = j.deptid WHERE j.fieldid = %u', $this->_db->prefix('xhelp_departments'), $this->_db->prefix('xhelp_ticket_field_departments'), $field);
        $ret   = $this->_db->query($sql);
        $arr   = [];

        if ($ret) {
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
        }

        return $arr;
    }

    /**
     * Get every field in a department
     *
     * @param  int  $dept      Department ID
     * @param  bool $id_as_key Should object ID be used as array key?
     * @return array array of {@Link Xhelp\TicketField} objects
     * @access public
     */
    public function fieldsByDepartment($dept, $id_as_key = false)
    {
        $dept = (int)$dept;
        $sql  = sprintf('SELECT f.* FROM `%s` f INNER JOIN %s j ON f.id = j.fieldid WHERE j.deptid = %u ORDER BY f.weight', $this->_db->prefix('xhelp_ticket_fields'), $this->_db->prefix('xhelp_ticket_field_departments'), $dept);
        $ret  = $this->_db->query($sql);
        $arr  = [];

        if ($ret) {
            while (false !== ($temp = $this->_db->fetchArray($ret))) {
                $field = $this->_hField->create();
                $field->assignVars($temp);
                if ($id_as_key) {
                    $arr[$field->getVar('id')] = $field;
                } else {
                    $arr[] = $field;
                }
                unset($temp);
            }
        }

        return $arr;
    }

    /**
     * Add the given field to the given department
     *
     * @param      $field
     * @param  int $deptid Department ID
     * @return bool True if successful, False if not
     * @internal param mixed $staff single or array of uids or <a href='psi_element://Xhelp\TicketField'>Xhelp\TicketField</a> objects objects
     * @access   public
     */
    public function addFieldToDepartment(&$field, $deptid)
    {
        if (!is_array($field)) {
            $ret = $this->_addMembership($field, $deptid);
        } else {
            foreach ($field as $var) {
                $ret = $this->_addMembership($var, $deptid);
                if (!$ret) {
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Add the given department(s) to the given field
     *
     * @param mixed $dept  single or array of department id's or {@Link Xhelp\Department} objects
     * @param int   $field Field ID
     * @retnr  bool True if successful, False if not
     * @access public
     * @return bool
     */
    public function addDepartmentToField($dept, $field)
    {
        if (!is_array($dept)) {
            $ret = $this->_addMembership($field, $dept);
        } else {
            foreach ($dept as $var) {
                $ret = $this->_addMembership($field, $var);
                if (!$ret) {
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Remove the given field(s) from the given department
     *
     * @param  mixed $field  single or array of field ids or {@link Xhelp\TicketField} objects
     * @param  int   $deptid Department ID
     * @return bool  True if successful, False if not
     * @access public
     */
    public function removeFieldFromDept(&$field, $deptid)
    {
        if (!is_array($field)) {
            $ret = $this->_removeMembership($field, $deptid);
        } else {
            foreach ($field as $var) {
                $ret = $this->_removeMembership($var, $deptid);
                if (!$ret) {
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Remove the given department(s) from the given field
     *
     * @param  mixed $dept  single or array of department id's or {@link Xhelp\Department} objects
     * @param  int   $field Field ID
     * @return bool  True if successful, False if not
     * @access public
     */
    public function removeDeptFromField(&$dept, $field)
    {
        if (!is_array($dept)) {
            $ret = $this->_removeMembership($field, $dept);
        } else {
            foreach ($dept as $var) {
                $ret = $this->_removeMembership($field, $var);
                if (!$ret) {
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Remove All Departments from a particular field
     * @param  int $field Field ID
     * @return bool True if successful, False if not
     * @access public
     */
    public function removeFieldFromAllDept($field)
    {
        $field = (int)$field;
        $crit  = new \Criteria('fieldid', $field);
        $ret   = $this->deleteAll($crit);

        return $ret;
    }

    /**
     * Remove All Departments from a particular field
     * @param $dept
     * @return bool True if successful, False if not
     * @internal param int $field Field ID
     * @access   public
     */

    public function removeDeptFromAllFields($dept)
    {
        $dept = (int)$dept;
        $crit = new \Criteria('deptid', $dept);
        $ret  = $this->deleteAll($crit);

        return $ret;
    }

    /**
     * @param null $criteria
     * @param bool $force
     * @return bool
     */
    public function deleteAll($criteria = null, $force = false)
    {
        $sql = 'DELETE FROM ' . $this->_db->prefix('xhelp_ticket_field_departments');
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if (!$force) {
            $result = $this->_db->query($sql);
        } else {
            $result = $this->_db->queryF($sql);
        }
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Add a field to a department
     *
     * @param  mixed $field fieldid or {@Link Xhelp\TicketField} object
     * @param  mixed $dept  deptid or {@Link Xhelp\Department} object
     * @return bool  True if Successful, False if not
     * @access private
     */
    public function _addMembership(&$field, &$dept)
    {
        $fieldid = $deptid = 0;

        if (is_object($field)) {
            $fieldid = $field->getVar('id');
        } else {
            $fieldid = (int)$field;
        }

        if (is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = (int)$dept;
        }

        $ret = $this->_addJoinerRecord($fieldid, $deptid);

        return $ret;
    }

    /**
     * @param $fieldid
     * @param $deptid
     * @return mixed
     */
    public function _addJoinerRecord($fieldid, $deptid)
    {
        $sql = sprintf('INSERT INTO %s (fieldid, deptid) VALUES (%u, %u)', $this->_db->prefix('xhelp_ticket_field_departments'), $fieldid, $deptid);
        $ret = $this->_db->query($sql);

        return $ret;
    }

    /**
     * @param $field
     * @param $dept
     * @return mixed
     */
    public function _removeMembership(&$field, &$dept)
    {
        $fieldid = $deptid = 0;
        if (is_object($field)) {
            $fieldid = $field->getVar('id');
        } else {
            $fieldid = (int)$field;
        }

        if (is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = (int)$dept;
        }

        $ret = $this->_removeJoinerRecord($fieldid, $deptid);

        return $ret;
    }

    /**
     * @param $fieldid
     * @param $deptid
     * @return mixed
     */
    public function _removeJoinerRecord($fieldid, $deptid)
    {
        $sql = sprintf('DELETE FROM `%s` WHERE fieldid = %u AND deptid = %u', $this->_db->prefix('xhelp_ticket_field_departments'), $fieldid, $deptid);
        $ret = $this->_db->query($sql);

        return $ret;
    }
}
