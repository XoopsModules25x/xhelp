<?php namespace Xoopsmodules\xhelp;

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

use Xoopsmodules\xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';


/**
 * xhelp\ResponsesHandler class
 *
 * Response Handler for xhelp\Responses class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class ResponsesHandler extends xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = Responses::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_responses';

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
     * @param $obj
     * @return string
     */
    public function _insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf('INSERT INTO %s (id, uid, ticketid, message, timeSpent, updateTime, userIP, private)
            VALUES (%u, %u, %u, %s, %u, %u, %s, %u)', $this->_db->prefix($this->_dbtable), $id, $uid, $ticketid, $this->_db->quoteString($message), $timeSpent, time(), $this->_db->quoteString($userIP), $private);

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

        $sql = sprintf('UPDATE %s SET uid = %u, ticketid = %u, message = %s, timeSpent = %u,
            updateTime = %u, userIP = %s, private = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $ticketid, $this->_db->quoteString($message), $timeSpent, time(), $this->_db->quoteString($userIP), $private, $id);

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

    /**
     * delete a response from the database
     *
     * @param \XoopsObject $obj       reference to the {@link xhelpResponse}
     *                                obj to delete
     * @param  bool        $force
     * @return bool FALSE if failed.
     * @access  public
     */
    public function delete(\XoopsObject $obj, $force = false)
    {

        // Remove file associated with this response
        $hFiles = new xhelp\FileHandler($GLOBALS['xoopsDB']);
        $crit   = new \CriteriaCompo(new \Criteria('ticketid', $obj->getVar('ticketid')));
        $crit->add(new \Criteria('responseid', $obj->getVar('responseid')));
        if (!$hFiles->deleteAll($crit)) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * Get number of responses by staff members
     *
     * @param  int $ticketid ticket to get count
     * @return int Number of staff responses
     * @access  public
     */
    public function getStaffResponseCount($ticketid)
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s r INNER JOIN %s s ON r.uid = s.uid WHERE r.ticketid = %u', $this->_db->prefix($this->_dbtable), $this->_db->prefix('xhelp_staff'), $ticketid);

        $ret = $this->_db->query($sql);

        list($count) = $this->_db->fetchRow($ret);

        return $count;
    }

    /**
     * Get number of responses by ticketid
     *
     * @param  array $tickets where ticketid is key
     * @return array key = ticketid, value = response count
     * @access public
     */
    public function getResponseCounts($tickets)
    {
        if (is_array($tickets)) {
            //$crit = new \Criteria('ticketid', "(". implode(array_keys($tickets), ',') .")", 'IN');
            $sql = sprintf('SELECT COUNT(*) AS numresponses, ticketid FROM %s WHERE ticketid IN (%s) GROUP BY ticketid', $this->_db->prefix($this->_dbtable), implode(array_keys($tickets), ','));
        } else {
            return false;
        }
        $result = $this->_db->query($sql);

        if (!$result) {
            return false;
        }

        // Add each returned record to the result array
        while ($myrow = $this->_db->fetchArray($result)) {
            $tickets[$myrow['ticketid']] = $myrow['numresponses'];
        }

        return $tickets;
    }
}
