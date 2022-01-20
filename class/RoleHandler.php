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
 * @author       Eric Juden <ericj@epcusa.com>
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
    public $dbtable = 'xhelp_roles';

    /**
     * Constructor
     *
     * @param \XoopsDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        $this->helper = Helper::getInstance();
        parent::init($db);
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

        $sql = \sprintf('INSERT INTO `%s` (id, NAME, description, tasks) VALUES (%u, %s, %s, %u)', $this->db->prefix($this->dbtable), $id, $this->db->quoteString($name), $this->db->quoteString($description), $tasks);

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

        $sql = \sprintf('UPDATE `%s` SET NAME = %s, description = %s, tasks = %u WHERE id = %u', $this->db->prefix($this->dbtable), $this->db->quoteString($name), $this->db->quoteString($description), $tasks, $id);

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
     * delete a role from the database
     *
     * @param \XoopsObject $object    reference to the {@link Role}
     *                                obj to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $object, $force = false): bool
    {
        // Remove staff roles from db first
        $staffRoleHandler = $this->helper->getHandler('StaffRole');
        if (!$staffRoleHandler->deleteAll(new \Criteria('roleid', $object->getVar('id')))) {
            return false;
        }

        $ret = parent::delete($object, $force);

        return $ret;
    }

    /**
     * @param int $task
     * @return array
     */
    public function getRolesByTask(int $task): array
    {
        $task = $task;

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
