<?php
//$Id: ticketList.php,v 1.8 2005/11/21 15:47:47 eric_juden Exp $
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
 * xhelpTicketList class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpTicketList extends XoopsObject {
    function xhelpTicketList($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('searchid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('weight', XOBJ_DTYPE_INT, null, false);

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
 * xhelpTicketListHandler class
 *
 * TicketList Handler for xhelpTicketList class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpTicketListHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpticketlist';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_ticket_lists';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpTicketListHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, uid, searchid, weight) VALUES (%u, %d, %u, %u)",
        $this->_db->prefix($this->_dbtable), $id, $uid, $searchid, $weight);
        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET uid = %d, searchid = %u, weight = %u WHERE id = %u",
        $this->_db->prefix($this->_dbtable), $uid, $searchid, $weight, $id);
        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
        return $sql;
    }

    // Weight of last ticketList(from staff) and +1
    function createNewWeight($uid)
    {
        $uid = intval($uid);

        $crit = new CriteriaCompo(new Criteria('uid', $uid), 'OR');
        $crit->add(new Criteria('uid', XHELP_GLOBAL_UID), 'OR');
        $crit->setSort('weight');
        $crit->setOrder('desc');
        $crit->setLimit(1);
        $ticketList =& $this->getObjects($crit);
        $weight = ((is_object($ticketList[0])) ? $ticketList[0]->getVar('weight') : 0);
        return $weight + 1;
    }

    function changeWeight($listID, $up = true)
    {
        $listID = intval($listID);
        $ticketList =& $this->get($listID);     // Get ticketList being changed
        $origTicketWeight = $ticketList->getVar('weight');
        $crit = new Criteria('weight', $origTicketWeight, (($up) ? '<' : '>'));
        $crit->setSort('weight');
        $crit->setOrder(($up) ? 'DESC' : 'ASC');
        $crit->setLimit(1);

        $changeTicketList =& $this->getObject($crit);               // Get ticketList being changed with
        $newTicketWeight = $changeTicketList->getVar('weight');

        $ticketList->setVar('weight', $newTicketWeight);
        if($this->insert($ticketList, true)){      // If first one succeeds, change 2nd number
            $changeTicketList->setVar('weight', $origTicketWeight);
            if(!$this->insert($changeTicketList, true)){
                return false;
            }
        } else {
            return false;
        }
    }

    function &getObject($criteria = null)
    {
        $ret    = array();
        $limit  = $start = 0;
        $sql    = $this->_selectQuery($criteria);
        $id     = $this->_idfield;

        if (isset($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        $numrows = $this->_db->getRowsNum($result);
        if($numrows == 1) {
            $obj = new $this->classname($this->_db->fetchArray($result));
            return $obj;
        } else {
            return false;
        }
    }

    function &getListsByUser($uid)
    {
        $uid = intval($uid);
        $crit = new CriteriaCompo(new Criteria('uid', $uid), 'OR');
        $crit->add(new Criteria('uid', XHELP_GLOBAL_UID), 'OR');
        $crit->setSort('weight');
        $ret =& $this->getObjects($crit);
        return $ret;
    }

    function createStaffGlobalLists($uid)
    {
        $hSavedSearches =& xhelpGetHandler('savedSearch');
        $uid = intval($uid);

        $crit = new Criteria('uid', XHELP_GLOBAL_UID);
        $crit->setSort('id');
        $crit->setOrder('ASC');
        $globalSearches =& $hSavedSearches->getObjects($crit, true);
        $i = 1;
        foreach($globalSearches as $search){
            $list =& $this->create();
            $list->setVar('uid', $uid);
            $list->setVar('searchid', $search->getVar('id'));
            $list->setVar('weight', $i);
            $ret = $this->insert($list, true);
            $i++;
        }
        return $ret;
    }
}


?>