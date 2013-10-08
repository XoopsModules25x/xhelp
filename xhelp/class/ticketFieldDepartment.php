<?php
//$Id: ticketFieldDepartment.php,v 1.5 2005/10/03 19:13:08 eric_juden Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //

class xhelpTicketFieldDepartmentHandler
{
    var $_db;
    var $_hField;
    var $_hDept;

    /**
     * Constructor
     *
     * @param object &$db {@Link XoopsDatabase}
     * @access public
     */
    function xhelpTicketFieldDepartmentHandler(&$db)
    {
        $this->_db =& $db;
        $this->_hField =& xhelpGetHandler('ticketField');
        $this->_hDept  =& xhelpGetHandler('department');
    }


    /**
     * Get every department a field is "in"
     *
     * @param int $field Field ID
     * @param bool $id_as_key Should object ID be used as array key?
     * @return array array of {@Link xhelpDepartment} objects
     * @access public
     */
    function departmentsByField($field, $id_as_key = false)
    {
        $field = intval($field);
        $sql   = sprintf('SELECT d.* FROM %s d INNER JOIN %s j ON d.id = j.deptid WHERE j.fieldid = %u',
        $this->_db->prefix('xhelp_departments'), $this->_db->prefix('xhelp_ticket_field_departments'), $field);
        $ret = $this->_db->query($sql);
        $arr = array();

        if($ret){
            while ($temp = $this->_db->fetchArray($ret)) {
                $dept =& $this->_hDept->create();
                $dept->assignVars($temp);
                if ($id_as_key) {
                    $arr[$dept->getVar('id')] =& $dept;
                } else {
                    $arr[] =& $dept;
                }
                unset($temp);

            }
        }

        return $arr;
    }


    /**
     * Get every field in a department
     *
     * @param int $dept Department ID
     * @param bool $id_as_key Should object ID be used as array key?
     * @return array array of {@Link xhelpTicketField} objects
     * @access public
     */
    function fieldsByDepartment($dept, $id_as_key = false)
    {
        $dept = intval($dept);
        $sql   = sprintf('SELECT f.* FROM %s f INNER JOIN %s j ON f.id = j.fieldid WHERE j.deptid = %u ORDER BY f.weight',
        $this->_db->prefix('xhelp_ticket_fields'), $this->_db->prefix('xhelp_ticket_field_departments'), $dept);
        $ret = $this->_db->query($sql);
        $arr = array();

        if($ret){
            while ($temp = $this->_db->fetchArray($ret)) {
                $field =& $this->_hField->create();
                $field->assignVars($temp);
                if ($id_as_key) {
                    $arr[$field->getVar('id')] =& $field;
                } else {
                    $arr[] =& $field;
                }
                unset($temp);

            }
        }
        return $arr;
    }


    /**
     * Add the given field to the given department
     *
     * @param mixed $staff single or array of uids or {@link xhelpTicketField} objects
     * @param int $deptid Department ID
     * @return bool True if successful, False if not
     * @access public
     */
    function addFieldToDepartment(&$field, $deptid)
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
     * @param mixed $dept single or array of department id's or {@Link xhelpDepartment} objects
     * @param int $field Field ID
     * @retnr bool True if successful, False if not
     * @access public
     */
    function addDepartmentToField($dept, $field)
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
     * @param mixed $field single or array of field ids or {@link xhelpTicketField} objects
     * @param int $deptid Department ID
     * @return bool True if successful, False if not
     * @access public
     */
    function removeFieldFromDept(&$field, $deptid)
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
     * @param mixed $dept single or array of department id's or {@link xhelpDepartment} objects
     * @param int $field Field ID
     * @return bool True if successful, False if not
     * @access public
     */
    function removeDeptFromField(&$dept, $field)
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
     * @param int $field Field ID
     * @return bool True if successful, False if not
     * @access public
     */
    function removeFieldFromAllDept($field)
    {
        $field = intval($field);
        $crit = new Criteria('fieldid', $field);
        $ret = $this->deleteAll($crit);
        return $ret;
    }

    /**
     * Remove All Departments from a particular field
     * @param int $field Field ID
     * @return bool True if successful, False if not
     * @access public
     */

    function removeDeptFromAllFields($dept)
    {
        $dept = intval($dept);
        $crit = new Criteria('deptid', $dept);
        $ret = $this->deleteAll($crit);
        return $ret;
    }


    function deleteAll($criteria=null, $force=false)
    {
        $sql = 'DELETE FROM '.$this->_db->prefix('xhelp_ticket_field_departments');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
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
     * @param mixed $field fieldid or {@Link xhelpTicketField} object
     * @param mixed $dept deptid or {@Link xhelpDepartment} object
     * @return bool True if Successful, False if not
     * @access private
     */
    function _addMembership(&$field, &$dept)
    {
        $fieldid = $deptid = 0;

        if (is_object($field)) {
            $fieldid = $field->getVar('id');
        } else {
            $fieldid = intval($field);
        }

        if (is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = intval($dept);
        }

        $ret = $this->_addJoinerRecord($fieldid, $deptid);
        return $ret;
    }

    function _addJoinerRecord($fieldid, $deptid)
    {
        $sql = sprintf('INSERT INTO %s (fieldid, deptid) VALUES (%u, %u)',
        $this->_db->prefix('xhelp_ticket_field_departments'), $fieldid, $deptid);
        $ret = $this->_db->query($sql);
        return $ret;
    }

    function _removeMembership(&$field, &$dept)
    {
        $fieldid = $deptid = 0;
        if (is_object($field)) {
            $fieldid = $field->getVar('id');
        } else {
            $fieldid = intval($field);
        }

        if (is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = intval($dept);
        }

        $ret = $this->_removeJoinerRecord($fieldid, $deptid);
        return $ret;
    }

    function _removeJoinerRecord($fieldid, $deptid)
    {
        $sql = sprintf('DELETE FROM %s WHERE fieldid = %u AND deptid = %u',
        $this->_db->prefix('xhelp_ticket_field_departments'), $fieldid, $deptid);
        $ret = $this->_db->query($sql);
        return $ret;
    }


}


?>