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
 * @author       Eric Juden <ericj@epcusa.com>
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
    public $dbtable = 'xhelp_staffroles';

    private const TABLE = 'xhelp_staffroles';
    private const ENTITY = StaffRole::class;
    private const ENTITYNAME = 'StaffRole';
    private const KEYNAME = 'uid';
    private const IDENTIFIER = 'roleid';

    /**
     * Constructor
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
    {
        $this->init($db);
        $this->helper = Helper::getInstance();
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * @param int      $id
     * @param int|null $roleid
     * @param int|null $deptid
     * @return array|bool
     */
    public function get($id = null, $fields = null, int $roleid = null, int $deptid = null)
    {
        $criteria = new \CriteriaCompo(new \Criteria('uid', (string)$id));
        $criteria->add(new \Criteria('roleid', (string)$roleid));
        $criteria->add(new \Criteria('deptid', (string)$deptid));

        if (!$role = $this->getObjects($criteria)) {
            return false;
        }

        return $role;
    }

    /**
     * @param int  $uid
     * @param bool $id_as_key
     * @return array|bool
     */
    public function &getObjectsByStaff(int $uid, bool $id_as_key = false)
    {
        $uid      = $uid;
        $criteria = new \Criteria('uid', (string)$uid);

        $arr = $this->getObjects($criteria, $id_as_key);

        if (0 == \count($arr)) {
            $arr = false;
        }

        return $arr;
    }

    /**
     * @param int $uid
     * @param int $roleid
     * @return bool
     */
    public function staffInRole(int $uid, int $roleid): bool
    {
        $criteria = new \CriteriaCompo(new \Criteria('uid', $uid));
        $criteria->add(new \Criteria('roleid', $roleid));

        if (!$role = $this->getObjects($criteria)) {
            return false;
        }

        return true;
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

        $sql = \sprintf('INSERT INTO `%s` (uid, roleid, deptid) VALUES (%u, %u, %u)', $this->db->prefix($this->dbtable), $uid, $roleid, $deptid);

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE uid = %u', $this->db->prefix($this->dbtable), $object->getVar('uid'));

        return $sql;
    }
}   // end of handler class
