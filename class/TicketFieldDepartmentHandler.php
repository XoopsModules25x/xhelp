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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       XOOPS Development Team
 */

/**
 * class TicketFieldDepartmentHandler
 */
class TicketFieldDepartmentHandler
{
    private $db;
    private $ticketFieldHandler;
    private $departmentHandler;

    /**
     * Constructor
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
    {
        $this->db     = $db;
        $this->helper = Helper::getInstance();
        /** @var \XoopsModules\Xhelp\TicketFieldHandler $this- >ticketFieldHandler */
        $this->ticketFieldHandler = $this->helper->getHandler('TicketField');
        /** @var \XoopsModules\Xhelp\DepartmentHandler $this- >ticketFieldHandler */
        $this->departmentHandler = $this->helper->getHandler('Department');
    }

    /**
     * Get every department a field is "in"
     *
     * @param int  $field     Field ID
     * @param bool $id_as_key Should object ID be used as array key?
     * @return array array of {@Link Department} objects
     */
    public function departmentsByField(int $field, bool $id_as_key = false): array
    {
        $field = $field;
        $sql   = \sprintf('SELECT d.* FROM `%s` d INNER JOIN %s j ON d.id = j.deptid WHERE j.fieldid = %u', $this->db->prefix('xhelp_departments'), $this->db->prefix('xhelp_ticket_field_departments'), $field);
        $ret   = $this->db->query($sql);
        $arr   = [];

        if ($ret) {
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
        }

        return $arr;
    }

    /**
     * Get every field in a department
     *
     * @param int  $dept      Department ID
     * @param bool $id_as_key Should object ID be used as array key?
     * @return array array of {@Link TicketField} objects
     */
    public function fieldsByDepartment(int $dept, bool $id_as_key = false): array
    {
        $dept   = $dept;
        $sql    = \sprintf('SELECT f.* FROM `%s` f INNER JOIN %s j ON f.id = j.fieldid WHERE j.deptid = %u ORDER BY f.weight', $this->db->prefix('xhelp_ticket_fields'), $this->db->prefix('xhelp_ticket_field_departments'), $dept);
        $result = $this->db->query($sql);
        $arr    = [];

        if ($this->db->getRowsNum($result) > 0) {
            while (false !== ($temp = $this->db->fetchArray($result))) {
                $field = $this->ticketFieldHandler->create();
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
     * @param array|TicketField $field
     * @param int               $deptid Department ID
     * @return bool True if successful, False if not
     * @internal param mixed $staff single or array of uids or TicketField object
     */
    public function addFieldToDepartment($field, int $deptid): bool
    {
        $ret = false;
        if (\is_array($field)) {
            foreach ($field as $var) {
                $ret = $this->addMembership($var, $deptid);
                if (!$ret) {
                    break;
                }
            }
        } else {
            $ret = $this->addMembership($field, $deptid);
        }

        return $ret;
    }

    /**
     * Add the given department(s) to the given field
     *
     * @param mixed $dept  single or array of department id's or {@Link Department} objects
     * @param int   $field Field ID
     * @retnr  bool True if successful, False if not
     * @return bool
     */
    public function addDepartmentToField($dept, int $field): bool
    {
        $ret = false;
        if (\is_array($dept)) {
            foreach ($dept as $var) {
                $ret = $this->addMembership($field, $var);
                if (!$ret) {
                    break;
                }
            }
        } else {
            $ret = $this->addMembership($field, $dept);
        }

        return $ret;
    }

    /**
     * Remove the given field(s) from the given department
     *
     * @param mixed $field  single or array of field ids or {@link TicketField} objects
     * @param int   $deptid Department ID
     * @return bool  True if successful, False if not
     */
    public function removeFieldFromDept($field, int $deptid): bool
    {
        $ret = false;
        if (\is_array($field)) {
            foreach ($field as $var) {
                $ret = $this->removeMembership($var, $deptid);
                if (!$ret) {
                    break;
                }
            }
        } else {
            $ret = $this->removeMembership($field, $deptid);
        }

        return $ret;
    }

    /**
     * Remove the given department(s) from the given field
     *
     * @param mixed $dept  single or array of department id's or {@link Department} objects
     * @param int   $field Field ID
     * @return bool  True if successful, False if not
     */
    public function removeDeptFromField($dept, int $field): bool
    {
        $ret = false;
        if (\is_array($dept)) {
            foreach ($dept as $var) {
                $ret = $this->removeMembership($field, $var);
                if (!$ret) {
                    break;
                }
            }
        } else {
            $ret = $this->removeMembership($field, $dept);
        }

        return $ret;
    }

    /**
     * Remove All Departments from a particular field
     * @param int $fieldId Field ID
     * @return bool True if successful, False if not
     */
    public function removeFieldFromAllDept(int $fieldId): bool
    {
        $ret = false;
        //        $field    = $field;
        $criteria = new \Criteria('fieldid', $fieldId);
        $ret      = $this->deleteAll($criteria);

        return $ret;
    }

    /**
     * Remove All Departments from a particular field
     * @param int $dept
     * @return bool True if successful, False if not
     * @internal param int $field Field ID
     */
    public function removeDeptFromAllFields(int $dept): bool
    {
        $ret      = false;
        $dept     = $dept;
        $criteria = new \Criteria('deptid', $dept);
        $ret      = $this->deleteAll($criteria);

        return $ret;
    }

    /**
     * @param \CriteriaElement|\CriteriaCompo|null $criteria
     * @param bool                                 $force
     * @return bool
     */
    public function deleteAll($criteria = null, bool $force = false): bool
    {
        $sql = 'DELETE FROM ' . $this->db->prefix('xhelp_ticket_field_departments');
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Add a field to a department
     *
     * @param mixed $field fieldid or {@Link TicketField} object
     * @param mixed $dept  deptid or {@Link Department} object
     * @return bool  True if Successful, False if not
     */
    public function addMembership($field, $dept): bool
    {
        $ret     = false;
        $fieldid = $deptid = 0;

        if (\is_object($field)) {
            $fieldid = $field->getVar('id');
        } else {
            $fieldid = (int)$field;
        }

        if (\is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = (int)$dept;
        }

        $ret = $this->addJoinerRecord($fieldid, $deptid);

        return $ret;
    }

    /**
     * @param int $fieldid
     * @param int $deptid
     * @return mixed
     */
    private function addJoinerRecord(int $fieldid, int $deptid)
    {
        $ret = false;
        $sql = \sprintf('INSERT INTO `%s` (fieldid, deptid) VALUES (%u, %u)', $this->db->prefix('xhelp_ticket_field_departments'), $fieldid, $deptid);
        $ret = $this->db->query($sql);

        return $ret;
    }

    /**
     * @param int|\XoopsModules\Xhelp\TicketField $field
     * @param int|\XoopsModules\Xhelp\Department  $dept
     * @return mixed
     */
    private function removeMembership($field, $dept)
    {
        $ret     = false;
        $fieldid = $deptid = 0;
        if (\is_object($field)) {
            $fieldid = $field->getVar('id');
        } else {
            $fieldid = (int)$field;
        }

        if (\is_object($dept)) {
            $deptid = $dept->getVar('id');
        } else {
            $deptid = (int)$dept;
        }

        $ret = $this->removeJoinerRecord($fieldid, $deptid);

        return $ret;
    }

    /**
     * @param int $fieldid
     * @param int $deptid
     * @return mixed
     */
    private function removeJoinerRecord(int $fieldid, int $deptid)
    {
        $ret = false;
        $sql = \sprintf('DELETE FROM `%s` WHERE fieldid = %u AND deptid = %u', $this->db->prefix('xhelp_ticket_field_departments'), $fieldid, $deptid);
        $ret = $this->db->query($sql);

        return $ret;
    }
}
