<?php
//$Id: mailEvent.php,v 1.5 2005/02/15 16:58:03 ackbarr Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');

/**
 * xhelpDepartment class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpMailEvent extends XoopsObject {
    function xhelpMailEvent($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('mbox_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('event_desc', XOBJ_DTYPE_TXTAREA, null, false, 65000);
        $this->initVar('event_class', XOBJ_DTYPE_INT, null, false);
        $this->initVar('posted', XOBJ_DTYPE_INT, null, false);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    function posted($format="l")
    {
        return formatTimestamp($this->getVar('posted'), $format);
    }
}   //end of class

/**
 * xhelpMailEventHandler class
 *
 * MailEvent Handler for xhelpMailEvent class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpMailEventHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpmailEvent';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_mailevent';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpMailEventHandler(&$db)
    {
        parent::init($db);
    }

     
    /**
     * Create a "select" SQL query
     * @param object $criteria {@link CriteriaElement} to match
     * @return	string SQL query
     * @access	private
     */
    function _selectQuery($criteria = null, $join = false)
    {
        if(!$join){
            $sql = sprintf('SELECT * FROM %s', $this->_db->prefix($this->_dbtable));
        } else {
            $sql = sprintf("SELECT e.* FROM %s e INNER JOIN %s d ON d.id = e.mbox_id", $this->_db->prefix('xhelp_mailevent'), $this->_db->prefix('xhelp_department_mailbox'));
        }

        if(isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' .$criteria->renderWhere();
            if($criteria->getSort() != '') {
                $sql .= ' ORDER BY ' . $criteria->getSort() . '
                    ' .$criteria->getOrder();
            }
        }
        return $sql;
    }



    /**
     * retrieve objects from the database
     *
     * @param object $criteria {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key Should the MailEvent ID be used as array key
     * @return array array of {@link xhelpMailEvent} objects
     * @access	public
     */
    function &getObjectsJoin($criteria = null, $id_as_key = false)
    {
        $ret    = array();
        $limit  = $start = 0;
        $sql    = $this->_selectQuery($criteria, true);
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
            if (!$id_as_key) {
                $ret[] =& $obj;
            } else {
                $ret[$obj->getVar('id')] =& $obj;
            }
            unset($obj);
        }
        return $ret;
    }

    function newEvent($mbox_id, $desc, $class)
    {
        $event =& $this->create();
        $event->setVar('mbox_id', $mbox_id);
        $event->setVar('event_desc', $desc);
        $event->setVar('event_class', $class);
        $event->setVar('posted', time());

        if(!$this->insert($event, true)){
            return false;
        }
        return true;
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, mbox_id, event_desc, event_class, posted) VALUES (%u, %u, %s, %u, %u)",
        $this->_db->prefix($this->_dbtable), $id, $mbox_id, $this->_db->quoteString($event_desc),
        $event_class, $posted);

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET mbox_id = %u, event_desc = %s, event_class = %u, posted = %u WHERE id = %u",
        $this->_db->prefix($this->_dbtable), $mbox_id, $this->_db->quoteString($event_desc), $event_class,
        $posted, $id);
        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
        return $sql;
    }

}
?>