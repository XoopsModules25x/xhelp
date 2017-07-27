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

require_once XHELP_CLASS_PATH . '/xhelpBaseObjectHandler.php';

/**
 * xhelpTicketEmails class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpTicketEmails extends XoopsObject
{
    /**
     * XHelpTicketEmails constructor.
     * @param null $id
     */
    public function __construct($id = null)
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
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class XHelpTicketEmailsHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelpticketemails';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_ticket_submit_emails';

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
     * @param $obj
     * @return string
     */
    public function _insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf('INSERT INTO %s (ticketid, uid, email, suppress) VALUES (%u, %u, %s, %u)', $this->_db->prefix($this->_dbtable), $ticketid, $uid, $this->_db->quoteString($email), $suppress);

        return $sql;
    }

    /**
     * @param null $criteria
     * @return string
     */
    public function _deleteQuery($criteria = null)
    {
        $sql = sprintf('DELETE FROM %s WHERE ticketid = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('ticketid'));

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

        $sql = sprintf('UPDATE %s SET suppress = %u WHERE ticketid = %u AND uid = %u AND email = %s', $this->_db->prefix($this->_dbtable), $suppress, $ticketid, $uid, $this->_db->quotestring($email));

        return $sql;
    }

    /**
     * retrieve objects from the database
     *
     * @param  object $criteria  {@link CriteriaElement} conditions to be met
     * @param  bool   $id_as_key Should the department ID be used as array key
     * @return array  array of {@link xhelpDepartment} objects
     * @access  public
     */
    public function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->_selectQuery($criteria);
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
            $obj                        = new $this->classname($myrow);
            $ret[$obj->getVar('email')] = $obj;
            unset($obj);
        }

        return $ret;
    }

    /**
     * retrieve objects from the database
     *
     * @param  object $criteria {@link CriteriaElement} conditions to be met
     * @return array array of <a href='psi_element://xhelpDepartment'>xhelpDepartment</a> objects
     * objects
     * @internal param bool $id_as_key Should the department ID be used as array key
     * @access   public
     */
    public function &getObjectsSortedByTicket($criteria = null)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->_selectQuery($criteria);
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
            $obj                           = new $this->classname($myrow);
            $ret[$obj->getVar('ticketid')] = $obj;
            unset($obj);
        }

        return $ret;
    }
}
