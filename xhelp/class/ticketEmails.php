<?php
//$Id: ticketEmails.php,v 1.7 2005/04/12 16:00:24 eric_juden Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');

/**
 * xhelpTicketEmails class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpTicketEmails extends XoopsObject {
    function xhelpTicketEmails($id = null)
    {
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('email', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('suppress', XOBJ_DTYPE_INT, null, false);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}   //end of class

/**
 * xhelpTicketEmailsHandler class
 *
 * Department Handler for xhelpDepartment class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpTicketEmailsHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpticketemails';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_ticket_submit_emails';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpTicketEmailsHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (ticketid, uid, email, suppress) VALUES (%u, %u, %s, %u)",
        $this->_db->prefix($this->_dbtable), $ticketid, $uid, $this->_db->quoteString($email), $suppress);

        return $sql;

    }

    function _deleteQuery($criteria = null)
    {
        $sql = sprintf("DELETE FROM %s WHERE ticketid = %u", $this->_db->prefix($this->_dbtable), $obj->getVar('ticketid'));
        return $sql;
    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET suppress = %u WHERE ticketid = %u AND uid = %u AND email = %s", $this->_db->prefix($this->_dbtable),
        $suppress, $ticketid, $uid, $this->_db->quotestring($email));
        return $sql;
    }

    /**
     * retrieve objects from the database
     *
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key Should the department ID be used as array key
     * @return array array of {@link xhelpDepartment} objects
     * @access	public
     */
    function &getObjects($criteria = null)
    {
        $ret    = array();
        $limit  = $start = 0;
        $sql    = $this->_selectQuery($criteria);
        if (isset($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while ($myrow = $this->_db->fetchArray($result)) {
            $obj = new $this->classname($myrow);
            $ret[$obj->getVar('email')] =& $obj;
            unset($obj);
        }
        return $ret;
    }

    /**
     * retrieve objects from the database
     *
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key Should the department ID be used as array key
     * @return array array of {@link xhelpDepartment} objects
     * @access	public
     */
    function &getObjectsSortedByTicket($criteria = null)
    {
        $ret    = array();
        $limit  = $start = 0;
        $sql    = $this->_selectQuery($criteria);
        if (isset($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while ($myrow = $this->_db->fetchArray($result)) {
            $obj = new $this->classname($myrow);
            $ret[$obj->getVar('ticketid')] =& $obj;
            unset($obj);
        }
        return $ret;
    }
}
?>