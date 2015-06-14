<?php
//$Id: departmentMailBox.php,v 1.10 2005/02/15 16:58:02 ackbarr Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');
require_once(XHELP_CLASS_PATH.'/mailbox.php');
require_once(XHELP_CLASS_PATH.'/mailboxPOP3.php');
/**
 * xhelpDepartmentMailBox class
 *
 * @author Nazar Aziz <nazar@panthersoftware.com>
 * @access public
 * @package xhelp
 */
class xhelpDepartmentMailBox extends XoopsObject
{
    var $_mBox;
    var $_errors;
    var $_msgCount;
    var $_curMsg;

    /**
     * Class Constructor
     *
     * @param  mixed $id ID of Mailbox or array containing mailbox info
     * @access public
     * @return void
     */
    function xhelpDepartmentMailBox($id = null)
    {

        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('emailaddress',XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('departmentid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('server', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('serverport', XOBJ_DTYPE_INT, null, false);
        $this->initVar('username', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('password', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('priority', XOBJ_DTYPE_INT, null, false);
        $this->initVar('mboxtype', XOBJ_DTYPE_INT, _XHELP_MAILBOXTYPE_POP3, false);
        $this->initVar('active', XOBJ_DTYPE_INT, true, true);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
        $this->_errors = array();
    }

    /**
     * Connect to Mailbox
     *
     * @return bool True if connected, False on Errors
     * @access public
     */
    function connect()
    {
        //Create an instance of the Proper xhelpMailBox object
        if (!isset($this->_mBox)) {
            if(!$this->_mBox = $this->_getMailBox($this->getVar('mboxtype'))) {
                $this->setErrors(_XHELP_MBOX_INV_BOXTYPE);

                return false;
            }
        }
        if (!$this->_mBox->connect($this->getVar('server'), $this->getVar('serverport'))) {
            $this->setErrors(_XHELP_MAILEVENT_DESC0);

            return false;
        }

        if (!$this->_mBox->login($this->getVar('username'), $this->getVar('password'))) {
            $this->setErrors(_XHELP_MBOX_ERR_LOGIN);

            return false;
        }
        //Reset Message Pointer/Message Count
        unset ($this->_msgCount);
        $this->_curMsg = 0;

        return true;
    }

    function disconnect()
    {
        return ($this->_mBox->disconnect());
    }

    function hasMessages()
    {
        return ($this->messageCount() > 0);
    }

    function &getMessage()
    {
        $msg = array();
        $this->_curMsg ++;
        if ($this->_curMsg > $this->_msgCount) {
            return false;
        }
        $msg['index']   = $this->_curMsg;
        //$msg['headers'] = $this->_mBox->getHeaders($this->_curMsg);
        $msg['msg']     = $this->_mBox->getMsg($this->_curMsg);

        //$msg['body']     = $this->_mBox->getBody($this->_curMsg);

        return $msg;
    }

    function messageCount()
    {
        if (! isset($this->_msgCount) ) {
            $this->_msgCount = $this->_mBox->messageCount();
        }

        return $this->_msgCount;
    }

    function _getMailBox($mboxType)
    {
        switch ($mboxType) {
            case _XHELP_MAILBOXTYPE_IMAP:
                return new xhelpMailBoxIMAP;
                break;
            case _XHELP_MAILBOXTYPE_POP3:
                return new xhelpMailBoxPOP3;
                break;
            default:
                return false;
        }
    }

    function deleteMessage($msg)
    {
        if (is_array($msg)) {
            if (isset($msg['index'])) {
                $msgid = intval($msg['index']);
            }
        } else {
            $msgid = intval($msg);
        }

        if (!isset($msgid)) {
            return false;
        }

        return $this->_mBox->deleteMessage($msgid);
    }
}

/**
 * xhelpDepartmentMailBoxHandler class
 *
 * Methods to work store / retrieve xhelpDepartmentMailBoxServer
 * objects from the database
 *
 * @author Nazar Aziz <nazar@panthersoftware.com>
 * @access public
 * @package xhelp
 */
class xhelpDepartmentMailBoxHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access private
     */
    var $classname = 'xhelpdepartmentmailbox';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_department_mailbox';

    /**
     * Constructor
     *
     * @param object $db reference to a xoopsDB object
     */
    function xhelpDepartmentMailBoxHandler(&$db)
    {
        parent::init($db);
    }

    /**
     * retrieve server list by department
     * @param  int   $depid department id
     * @return array array of {@link xhelpDepartmentMailBox}
     * @access public
     */
    function &getByDepartment($depid)
    {
        $ret = null;
        $depid = intval($depid);
        if ($depid > 0) {
            $crit = new Criteria('departmentid',$depid);
            $crit->setSort('priority');
            $total = $this->getCount($crit);
            //
            if ($total>0) {
                $ret =& $this->getObjects($crit);

                return $ret;
            }
        }

        return $ret;
    }

    function &getActiveMailboxes()
    {
        $crit = new Criteria('active', 1);
        $ret =& $this->getObjects($crit);

        return $ret;
    }

    /**
     * creates new email server entry for department
     *
     * @access public
     */
    function addEmailServer($depid)
    {
        $server =& $this->create();
        $server->setVar('departmentid', $depid);

        return $this->insert($server);
    }

    /**
     * remove an email server
     *
     * @param  object $obj   {@link xhelpDepartmentMailbox} Mailbox to delete
     * @param  bool   $force Should bypass XOOPS delete restrictions
     * @return bool   True on Successful delete
     * @access public
     */
    function delete(&$obj, $force = false)
    {
        //Remove all Mail Events for mailbox
        $hMailEvent =& xhelpGetHandler('mailEvent');
        $crit = new Criteria('mbox_id', $obj->getVar('id'));
        $hMailEvent->deleteAll($crit);

        $ret = parent::delete($obj, $force);

        return $ret;

    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf( 'insert into %s (id, departmentid, server, serverport, username, password, priority, emailaddress, mboxtype, active) values (%u, %u, %s, %u, %s, %s, %u, %s, %u, %u)',
        $this->_db->prefix($this->_dbtable), $id, $departmentid, $this->_db->quoteString($server), $serverport, $this->_db->quoteString($username), $this->_db->quoteString($password), $priority, $this->_db->quoteString($emailaddress), $mboxtype, $active);

        return $sql;
    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf( 'UPDATE %s set departmentid = %u, server = %s, serverport = %u, username = %s, password = %s, priority = %u, emailaddress = %s, mboxtype = %u, active = %u where id = %u',
        $this->_db->prefix($this->_dbtable), $departmentid, $this->_db->quoteString($server), $serverport, $this->_db->quoteString($username), $this->_db->quoteString($password), $priority, $this->_db->quoteString($emailaddress), $mboxtype, $active, $id);

        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

}
