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

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
// require_once XHELP_CLASS_PATH . '/NotificationService.php';

/**
 * Xhelp\Staff class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */

// require_once XHELP_CLASS_PATH . '/session.php';

/**
 * class Staff
 */
class Staff extends \XoopsObject
{
    /**
     * Xhelp\Staff constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('email', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('responseTime', XOBJ_DTYPE_INT, null, false);
        $this->initVar('numReviews', XOBJ_DTYPE_INT, null, false);
        $this->initVar('callsClosed', XOBJ_DTYPE_INT, null, false);
        $this->initVar('attachSig', XOBJ_DTYPE_INT, null, false);
        $this->initVar('rating', XOBJ_DTYPE_INT, null, false);
        $this->initVar('allDepartments', XOBJ_DTYPE_INT, null, false);
        $this->initVar('ticketsResponded', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('notify', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('permTimestamp', XOBJ_DTYPE_INT, 0, false);

        if (null !== $id) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * Used to make sure that the user has rights to do an action
     *
     * @param int   $task
     * @param mixed $depts integer/array of department id(s)
     *
     * @return TRUE if success, FALSE if failure
     *
     * @access public
     */
    public function checkRoleRights($task, $depts = 0)
    {
        $task = (int)$task;
        if (!is_array($depts)) { // Integer value, change $depts to an array with 1 element
            $depts   = (int)$depts;
            $dept_id = $depts;
            $depts   = [];
            $depts[] = $dept_id;
        }

        $_xhelpSession = new Xhelp\Session();

        if (!$rights = $_xhelpSession->get('xhelp_staffRights')) {
            $rights = $this->getAllRoleRights();
            $_xhelpSession->set('xhelp_staffRights', $rights);
        }

        foreach ($depts as $deptid) {
            if (isset($rights[$deptid])) {
                $hasRights = ($rights[$deptid]['tasks'] & (2 ** $task)) > 0;
                if (false === $hasRights) {
                    return false;
                }
            } else {
                //no permission in department
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve all role rights for current user
     */
    public function getAllRoleRights()
    {
        $perms      = [];
        $hStaff     = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);
        $hRole      = new Xhelp\RoleHandler($GLOBALS['xoopsDB']);
        $roles      = $hRole->getObjects(null, true);
        $staffRoles = $hStaff->getRoles($this->getVar('uid'));
        foreach ($staffRoles as $role) {
            $deptid = $role->getVar('deptid');
            $roleid = $role->getVar('roleid');

            if (isset($roles[$roleid])) {
                $perms[$deptid]['roles'][$roleid] = $roles[$roleid]->getVar('tasks');
                if (isset($perms[$deptid]['tasks'])) {
                    $perms[$deptid]['tasks'] |= (int)$roles[$roleid]->getVar('tasks');
                } else {
                    $perms[$deptid]['tasks'] = (int)$roles[$roleid]->getVar('tasks');
                }
            }
        }

        return $perms;
    }

    /**
     * @return bool
     */
    public function resetRoleRights()
    {
        $_xhelpSession = new Xhelp\Session();
        $_xhelpSession->del('xhelp_staffRights');

        return true;
    }
}
