<?php
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

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once XHELP_CLASS_PATH . '/xhelpBaseObjectHandler.php';

/**
 * xhelpStaffReview class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpStaffReview extends XoopsObject
{
    /**
     * XHelpStaffReview constructor.
     * @param null $id
     */
    public function __construct($id = null)
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
    public function posted()
    {
        return formatTimestamp($this->getVar('updateTime'));
    }
}

/**
 * xhelpStaffReviewHandler class
 *
 * StaffReview Handler for xhelpStaffReview class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class XHelpStaffReviewHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelpstaffreview';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_staffreview';

    /**
     * Constructor
     *
     * @param object|XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::init($db);
    }

    /**
     * retrieve a StaffReview object meeting certain criteria
     * @param  int $ticketid    ID of ticket
     * @param  int $responseid  ID of response
     * @param  int $submittedBy UID of ticket submitter
     * @return object (@link xhelpStaffReview}
     * @access public
     */
    public function &getReview($ticketid, $responseid, $submittedBy)
    {
        $ticketid    = (int)$ticketid;
        $responseid  = (int)$responseid;
        $submittedBy = (int)$submittedBy;

        $crit = new CriteriaCompo(new Criteria('ticketid', $ticketid));
        $crit->add(new Criteria('submittedBy', $submittedBy));
        $crit->add(new Criteria('responseid', $responseid));
        $review = [];
        if (!$review = $this->getObjects($crit)) {
            return false;
        } else {
            return $review;
        }
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

        $sql = sprintf('INSERT INTO %s (id, staffid, rating, ticketid, responseid, comments, submittedBy, userIP)
            VALUES (%u, %u, %u, %u, %u, %s, %u, %s)', $this->_db->prefix($this->_dbtable), $id, $staffid, $rating, $ticketid, $responseid, $this->_db->quoteString($comments), $submittedBy, $this->_db->quoteString($userIP));

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

        $sql = sprintf('UPDATE %s SET staffid = %u, rating = %u, ticketid = %u, responseid = %u, comments = %s, submittedBy = %u, userIP = %s
                WHERE id = %u', $this->_db->prefix($this->_dbtable), $staffid, $rating, $ticketid, $responseid, $this->_db->quoteString($comments), $submittedBy, $this->_db->quoteString($userIP), $id);

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
