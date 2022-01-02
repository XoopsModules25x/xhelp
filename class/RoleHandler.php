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

/**
 * class RoleHandler
 */
class RoleHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = Role::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_roles';

    /**
     * Constructor
     *
     * @param \XoopsDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::init($db);
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf('INSERT INTO `%s` (id, NAME, description, tasks) VALUES (%u, %s, %s, %u)', $this->_db->prefix($this->_dbtable), $id, $this->_db->quoteString($name), $this->_db->quoteString($description), $tasks);

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf('UPDATE `%s` SET NAME = %s, description = %s, tasks = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $this->_db->quoteString($name), $this->_db->quoteString($description), $tasks, $id);

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * delete a role from the database
     *
     * @param \XoopsObject $obj       reference to the {@link Role}
     *                                obj to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $obj, bool $force = false)
    {
        // Remove staff roles from db first
        $staffRoleHandler = new StaffRoleHandler($GLOBALS['xoopsDB']);
        if (!$staffRoleHandler->deleteAll(new \Criteria('roleid', $obj->getVar('id')))) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * @param $task
     * @return array
     */
    public function getRolesByTask($task): array
    {
        $task = (int)$task;

        // Get all roles
        $roles = $this->getObjects();

        $aRoles = [];
        foreach ($roles as $role) {
            if (($role->getVar('tasks') & (2 ** $task)) > 0) {
                $aRoles[$role->getVar('id')] = $role;
            }
        }

        return $aRoles;
    }
}
