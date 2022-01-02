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
 * class StaffRoleHandler
 */
class StaffRoleHandler extends BaseObjectHandler
{
    public $idfield = 'roleid';
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = StaffRole::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_staffroles';

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
     * @param int  $int_id
     * @param null $roleid
     * @param null $deptid
     * @return array|bool
     */
    public function get($int_id, $roleid = null, $deptid = null)
    {
        $criteria = new \CriteriaCompo('uid', $int_id);
        $criteria->add(new \Criteria('roleid', $roleid));
        $criteria->add(new \Criteria('deptid', $deptid));

        if (!$role = $this->getObjects($criteria)) {
            return false;
        }

        return $role;
    }

    /**
     * @param      $uid
     * @param bool $id_as_key
     * @return array|bool
     */
    public function &getObjectsByStaff($uid, $id_as_key = false)
    {
        $uid  = (int)$uid;
        $criteria = new \Criteria('uid', $uid);

        $arr = $this->getObjects($criteria, $id_as_key);

        if (0 == \count($arr)) {
            $arr = false;
        }

        return $arr;
    }

    /**
     * @param $uid
     * @param $roleid
     * @return bool
     */
    public function staffInRole($uid, $roleid): bool
    {
        $criteria = new \CriteriaCompo('uid', $uid);
        $criteria->add(new \Criteria('roleid', $roleid));

        if (!$role = $this->getObjects($criteria)) {
            return false;
        }

        return true;
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

        $sql = \sprintf('INSERT INTO `%s` (uid, roleid, deptid) VALUES (%u, %u, %u)', $this->_db->prefix($this->_dbtable), $uid, $roleid, $deptid);

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE uid = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('uid'));

        return $sql;
    }
}   // end of handler class
