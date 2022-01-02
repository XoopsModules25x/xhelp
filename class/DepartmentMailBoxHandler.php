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
// require_once XHELP_CLASS_PATH . '/mailbox.php';
// require_once XHELP_CLASS_PATH . '/mailboxPOP3.php';

/**
 * DepartmentMailBoxHandler class
 *
 * Methods to work store / retrieve DepartmentMailBoxServer
 * objects from the database
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 */
class DepartmentMailBoxHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = DepartmentMailBox::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_department_mailbox';

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
     * retrieve server list by department
     * @param int $depid department id
     * @return array array of {@link DepartmentMailBox}
     */
    public function &getByDepartment(int $depid): array
    {
        $ret   = [];
        $depid = $depid;
        if ($depid > 0) {
            $criteria = new \Criteria('departmentid', (string)$depid);
            $criteria->setSort('priority');
            $total = $this->getCount($criteria);

            if ($total > 0) {
                $ret = $this->getObjects($criteria);

                return $ret;
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function &getActiveMailboxes(): array
    {
        $criteria = new \Criteria('active', '1');
        $ret  = $this->getObjects($criteria);

        return $ret;
    }

    /**
     * creates new email server entry for department
     *
     * @param int $depid
     * @return bool
     */
    public function addEmailServer(int $depid)
    {
        $server = $this->create();
        $server->setVar('departmentid', $depid);

        return $this->insert($server);
    }

    /**
     * remove an email server
     *
     * @param \XoopsObject $obj         {@link DepartmentMailbox}
     *                                  Mailbox to delete
     * @param bool         $force       Should bypass XOOPS delete restrictions
     * @return bool True on Successful delete
     */
    public function delete(\XoopsObject $obj, bool $force = false)
    {
        //Remove all Mail Events for mailbox
        $mailEventHandler = new MailEventHandler($GLOBALS['xoopsDB']);
        $criteria             = new \Criteria('mbox_id', $obj->getVar('id'));
        $mailEventHandler->deleteAll($criteria);

        $ret = parent::delete($obj, $force);

        return $ret;
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

        $sql = \sprintf(
            'INSERT INTO `%s` (id, departmentid, SERVER, serverport, username, PASSWORD, priority, emailaddress, mboxtype, active) VALUES (%u, %u, %s, %u, %s, %s, %u, %s, %u, %u)',
            $this->_db->prefix($this->_dbtable),
            $id,
            $departmentid,
            $this->_db->quoteString($server),
            $serverport,
            $this->_db->quoteString($username),
            $this->_db->quoteString($password),
            $priority,
            $this->_db->quoteString($emailaddress),
            $mboxtype,
            $active
        );

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

        $sql = \sprintf(
            'UPDATE `%s` SET departmentid = %u, SERVER = %s, serverport = %u, username = %s, PASSWORD = %s, priority = %u, emailaddress = %s, mboxtype = %u, active = %u WHERE id = %u',
            $this->_db->prefix($this->_dbtable),
            $departmentid,
            $this->_db->quoteString($server),
            $serverport,
            $this->_db->quoteString($username),
            $this->_db->quoteString($password),
            $priority,
            $this->_db->quoteString($emailaddress),
            $mboxtype,
            $active,
            $id
        );

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
}
