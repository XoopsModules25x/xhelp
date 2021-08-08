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
// require_once XHELP_CLASS_PATH . '/mailbox.php';
// require_once XHELP_CLASS_PATH . '/mailboxPOP3.php';


/**
 * Xhelp\DepartmentMailBoxHandler class
 *
 * Methods to work store / retrieve Xhelp\DepartmentMailBoxServer
 * objects from the database
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @access  public
 * @package xhelp
 */
class DepartmentMailBoxHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access private
     */
    public $classname = DepartmentMailBox::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_department_mailbox';

    /**
     * Constructor
     *
     * @param \XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::init($db);
    }

    /**
     * retrieve server list by department
     * @param  int $depid department id
     * @return array array of {@link Xhelp\DepartmentMailBox}
     * @access public
     */
    public function &getByDepartment($depid)
    {
        $ret   = null;
        $depid = (int)$depid;
        if ($depid > 0) {
            $crit = new \Criteria('departmentid', $depid);
            $crit->setSort('priority');
            $total = $this->getCount($crit);
            //
            if ($total > 0) {
                $ret = $this->getObjects($crit);

                return $ret;
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function &getActiveMailboxes()
    {
        $crit = new \Criteria('active', 1);
        $ret  = $this->getObjects($crit);

        return $ret;
    }

    /**
     * creates new email server entry for department
     *
     * @access public
     * @param $depid
     * @return bool|void
     */
    public function addEmailServer($depid)
    {
        $server = $this->create();
        $server->setVar('departmentid', $depid);

        return $this->insert($server);
    }

    /**
     * remove an email server
     *
     * @param \XoopsObject $obj         {@link Xhelp\DepartmentMailbox}
     *                                  Mailbox to delete
     * @param  bool        $force       Should bypass XOOPS delete restrictions
     * @return bool True on Successful delete
     * @access public
     */
    public function delete(\XoopsObject $obj, $force = false)
    {
        //Remove all Mail Events for mailbox
        $hMailEvent = new Xhelp\MailEventHandler($GLOBALS['xoopsDB']);
        $crit       = new \Criteria('mbox_id', $obj->getVar('id'));
        $hMailEvent->deleteAll($crit);

        $ret = parent::delete($obj, $force);

        return $ret;
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

        $sql = sprintf(
            'INSERT INTO %s (id, departmentid, SERVER, serverport, username, PASSWORD, priority, emailaddress, mboxtype, active) VALUES (%u, %u, %s, %u, %s, %s, %u, %s, %u, %u)',
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
     * @param $obj
     * @return string
     */
    public function _updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf(
            'UPDATE %s SET departmentid = %u, SERVER = %s, serverport = %u, username = %s, PASSWORD = %s, priority = %u, emailaddress = %s, mboxtype = %u, active = %u WHERE id = %u',
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
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }
}
