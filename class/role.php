<?php
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

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}
require_once XHELP_CLASS_PATH . '/xhelpBaseObjectHandler.php';

/**
 * xhelpRole class
 *
 * Information about an individual role
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpRole extends XoopsObject
{
    /**
     * XHelpRole constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 35);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false, 1024);
        $this->initVar('tasks', XOBJ_DTYPE_INT, 0, false);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}   // end of class

/**
 * Class XHelpRoleHandler
 */
class XHelpRoleHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelprole';

    /**
     * DB Table Name
     *
     * @var string
     * @access  private
     */
    public $_dbtable = 'xhelp_roles';

    /**
     * Constructor
     *
     * @param object|XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::init($db);
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

        $sql = sprintf('INSERT INTO %s (id, NAME, description, tasks) VALUES (%u, %s, %s, %u)', $this->_db->prefix($this->_dbtable), $id, $this->_db->quoteString($name), $this->_db->quoteString($description), $tasks);

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

        $sql = sprintf('UPDATE %s SET NAME = %s, description = %s, tasks = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $this->_db->quoteString($name), $this->_db->quoteString($description), $tasks, $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * delete a role from the database
     *
     * @param object|XoopsObject $obj reference to the {@link xhelpRole}
     *                                obj to delete
     * @param  bool              $force
     * @return bool FALSE if failed.
     * @access  public
     */
    public function delete(XoopsObject $obj, $force = false)
    {
        // Remove staff roles from db first
        $hStaffRole = xhelpGetHandler('staffRole');
        if (!$hStaffRole->deleteAll(new Criteria('roleid', $obj->getVar('id')))) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * @param $task
     * @return array
     */
    public function getRolesByTask($task)
    {
        $task = (int)$task;

        // Get all roles
        $roles = $this->getObjects();

        $aRoles = [];
        foreach ($roles as $role) {
            if (($role->getVar('tasks') & pow(2, $task)) > 0) {
                $aRoles[$role->getVar('id')] = $role;
            }
        }

        return $aRoles;
    }
}
