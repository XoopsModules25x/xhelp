<?php
//$Id: staffReview.php,v 1.9 2005/02/15 16:58:03 ackbarr Exp $
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
 * xhelpStaffReview class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpStaffReview extends XoopsObject {
    function xhelpStaffReview($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('staffid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('rating', XOBJ_DTYPE_INT, null, false);
        $this->initVar('comments', XOBJ_DTYPE_TXTAREA, null, false, 1024);
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('responseid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('submittedBy', XOBJ_DTYPE_INT, null, false);
        $this->initVar('userIP', XOBJ_DTYPE_TXTBOX, null, false, 255);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * Gets a UNIX timestamp
     *
     * @return int Timestamp of last update
     * @access public
     */
    function posted()
    {
        return formatTimestamp($this->getVar('updateTime'));
    }
}

/**
 * xhelpStaffReviewHandler class
 *
 * StaffReview Handler for xhelpStaffReview class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpStaffReviewHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var string
     * @access	private
     */
    var $classname = 'xhelpstaffreview';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_staffreview';

    /**
     * Constructor
     *
     * @param object $db reference to a xoopsDB object
     */
    function xhelpStaffReviewHandler(&$db)
    {
        parent::init($db);
    }

    /**
     * retrieve a StaffReview object meeting certain criteria
     * @param  int    $ticketid    ID of ticket
     * @param  int    $responseid  ID of response
     * @param  int    $submittedBy UID of ticket submitter
     * @return object (@link xhelpStaffReview}
     * @access public
     */
    function &getReview($ticketid, $responseid, $submittedBy)
    {
        $ticketid = intval($ticketid);
        $responseid = intval($responseid);
        $submittedBy = intval($submittedBy);

        $crit = new CriteriaCompo(new Criteria('ticketid', $ticketid));
        $crit->add(new Criteria('submittedBy', $submittedBy));
        $crit->add(new Criteria('responseid', $responseid));
        $review = array();
        if(!$review =& $this->getObjects($crit)){
            return false;
        } else {
            return $review;
        }
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, staffid, rating, ticketid, responseid, comments, submittedBy, userIP)
            VALUES (%u, %u, %u, %u, %u, %s, %u, %s)", $this->_db->prefix($this->_dbtable), $id, $staffid, $rating,
        $ticketid, $responseid, $this->_db->quoteString($comments), $submittedBy, $this->_db->quoteString($userIP));

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET staffid = %u, rating = %u, ticketid = %u, responseid = %u, comments = %s, submittedBy = %u, userIP = %s
                WHERE id = %u", $this->_db->prefix($this->_dbtable), $staffid, $rating, $ticketid, $responseid,
        $this->_db->quoteString($comments), $submittedBy, $this->_db->quoteString($userIP), $id);

        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

}
