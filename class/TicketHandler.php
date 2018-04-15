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
//require_once  dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php';

global $xoopsUser;


/**
 * class TicketHandler
 */
class TicketHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = Ticket::class;

    /**
     * DB Table Name
     *
     * @var string
     * @access  private
     */
    public $_dbtable = 'xhelp_tickets';

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
     * retrieve an object from the database, based on. use in child classes
     * @param  int $id ID
     * @return mixed object if id exists, false if not
     * @access public
     */
    public function get($id)
    {
        $id = (int)$id;
        if ($id > 0) {
            $sql = $this->_selectQuery(new \Criteria('id', $id, '=', 't'));
            if (!$result = $this->_db->query($sql)) {
                return false;
            }
            $numrows = $this->_db->getRowsNum($result);
            if (1 == $numrows) {
                $obj = new $this->classname($this->_db->fetchArray($result));

                return $obj;
            }
        }

        return false;
    }

    /**
     * find a ticket based on a hash
     *
     * @param  text $hash
     * @return bool object
     * @access public
     */
    public function getTicketByHash($hash)
    {
        $sql = $this->_selectQuery(new \Criteria('emailHash', $hash, '=', 't'));
        if (!$result = $this->_db->query($sql)) {
            return false;
        }
        $numrows = $this->_db->getRowsNum($result);
        if (1 == $numrows) {
            $obj = new $this->classname($this->_db->fetchArray($result));

            return $obj;
        }
    }

    /**
     * Retrieve the list of departments for the specified tickets
     * @param  mixed $tickets can be a single value or array consisting of either ticketids or ticket objects
     * @return array array of integers representing the ids of each department
     * @access public
     */
    public function getTicketDepartments($tickets)
    {
        $a_tickets = [];
        $a_depts   = [];
        if (is_array($tickets)) {
            foreach ($tickets as $ticket) {
                if (is_object($ticket)) {
                    $a_tickets[] = $ticket->getVar('id');
                } else {
                    $a_tickets[] = (int)$ticket;
                }
            }
        } else {
            if (is_object($tickets)) {
                $a_tickets[] = $tickets->getVar('id');
            } else {
                $a_tickets[] = (int)$tickets;
            }
        }

        $sql = sprintf('SELECT DISTINCT department FROM `%s` WHERE id IN (%s)', $this->_db->prefix('xhelp_tickets'), implode($a_tickets, ','));
        $ret = $this->_db->query($sql);

        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $a_depts[] = $temp['department'];
        }

        return $a_depts;
    }

    /**
     * @param      $crit
     * @param bool $id_as_key
     * @param bool $hasCustFields
     * @return array
     */
    public function &getObjectsByStaff($crit, $id_as_key = false, $hasCustFields = false)
    {
        $sql = $this->_selectQuery($crit, true, $hasCustFields);
        if (is_object($crit)) {
            $limit = $crit->getLimit();
            $start = $crit->getStart();
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = [];
        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $tickets = $this->create();
            $tickets->assignVars($temp);
            if ($id_as_key) {
                $arr[$tickets->getVar('id')] = $tickets;
            } else {
                $arr[] = $tickets;
            }
            unset($tickets);
        }

        return $arr;
    }

    /**
     * @param      $uid
     * @param bool $id_as_key
     * @return array
     */
    public function &getMyUnresolvedTickets($uid, $id_as_key = false)
    {
        $uid = (int)$uid;

        // Get all ticketEmail objects where $uid is found
        $hTicketEmails = new Xhelp\TicketEmailsHandler($GLOBALS['xoopsDB']);
        $crit          = new \Criteria('uid', $uid);
        $ticketEmails  = $hTicketEmails->getObjectsSortedByTicket($crit);

        // Get friendly array of all ticketids needed
        $aTicketEmails = [];
        foreach ($ticketEmails as $ticketEmail) {
            $aTicketEmails[$ticketEmail->getVar('ticketid')] = $ticketEmail->getVar('ticketid');
        }
        unset($ticketEmails);

        // Get unresolved statuses and filter out the resolved statuses
        $hStatus   = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
        $crit      = new \Criteria('state', 1);
        $statuses  = $hStatus->getObjects($crit, true);
        $aStatuses = [];
        foreach ($statuses as $status) {
            $aStatuses[$status->getVar('id')] = $status->getVar('id');
        }
        unset($statuses);

        // Get array of tickets.
        // Only want tickets that are unresolved.
        $crit = new \CriteriaCompo(new \Criteria('t.id', '(' . implode(array_keys($aTicketEmails), ',') . ')', 'IN'));
        $crit->add(new \Criteria('t.status', '(' . implode(array_keys($aStatuses), ',') . ')', 'IN'));
        $tickets = $this->getObjects($crit, $id_as_key);

        // Return all tickets
        return $tickets;
    }

    /**
     * @param      $state
     * @param bool $id_as_key
     * @return array
     */
    public function getObjectsByState($state, $id_as_key = false)
    {
        $crit = new \Criteria('state', (int)$state, '=', 's');
        $sql  = $this->_selectQuery($crit, true);
        if (is_object($crit)) {
            $limit = $crit->getLimit();
            $start = $crit->getStart();
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = [];
        while (false !== ($temp = $this->_db->fetchArray($ret))) {
            $tickets = $this->create();
            $tickets->assignVars($temp);
            if ($id_as_key) {
                $arr[$tickets->getVar('id')] = $tickets;
            } else {
                $arr[] = $tickets;
            }
            unset($tickets);
        }

        return $arr;
    }

    /**
     * @param      $criteria
     * @param bool $hasCustFields
     * @return int
     */
    public function getCountByStaff($criteria, $hasCustFields = false)
    {
        if (!$hasCustFields) {
            $sql = sprintf('SELECT COUNT(*) AS TicketCount FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s ON t.status = s.id', $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jstaffdept'), $this->_db->prefix('xhelp_status'));
        } else {
            $sql = sprintf(
                'SELECT COUNT(*) AS TicketCount FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s ON t.status = s.id INNER JOIN %s f ON t.id = f.ticketid ',
                $this->_db->prefix('xhelp_tickets'),
                $this->_db->prefix('xhelp_jstaffdept'),
                           $this->_db->prefix('xhelp_status'),
                $this->_db->prefix('xhelp_ticket_values')
            );
        }

        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if (!$result = $this->_db->query($sql)) {
            return 0;
        }
        list($count) = $this->_db->fetchRow($result);

        return $count;
    }

    /**
     * Get all tickets a staff member is in dept
     * @param  int    $uid   staff user id
     * @param  int    $mode  One of the '_QRY_STAFF_{X}' constants
     * @param  int    $start first record to return
     * @param  int    $limit number of records to return
     * @param  string $sort  Sort Field
     * @param  string $order Sort Order
     * @return array  array of {@link Xhelp\Ticket}> objects
     * @access public
     * @todo   Filter by Department, Status
     */
    public function getStaffTickets($uid, $mode = -1, $start = 0, $limit = 0, $sort = '', $order = '')
    {
        $uid  = (int)$uid;
        $arr  = [];
        $crit = new \CriteriaCompo();
        $crit->setLimit((int)$limit);
        $crit->setStart((int)$start);
        switch ($mode) {
            case XHELP_QRY_STAFF_HIGHPRIORITY:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->add(new \Criteria('ownership', 0, '=', 't'));
                $crit->setSort('t.priority, t.posted');
                break;

            case XHELP_QRY_STAFF_NEW:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                $crit->add(new \Criteria('ownership', 0, '=', 't'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->setSort('t.posted');
                $crit->setOrder('DESC');
                break;

            case XHELP_QRY_STAFF_MINE:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                $crit->add(new \Criteria('ownership', $uid, '=', 't'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->setSort('t.posted');
                break;

            case XHELP_QRY_STAFF_ALL:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                break;

            default:
                return $arr;
                break;
        }

        return $this->getObjectsByStaff($crit);
    }

    /**
     * Get number of tickets based on staff membership
     * @param  int $uid staff user id
     * @param  int $mode
     * @return int Number of tickets
     * @access public
     * @todo   Filter by Department, Status
     */
    public function getStaffTicketCount($uid, $mode = -1)
    {
        $crit = new \CriteriaCompo();
        switch ($mode) {
            case XHELP_QRY_STAFF_HIGHPRIORITY:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                $crit->add(new \Criteria('status', 2, '<', 't'));
                $crit->add(new \Criteria('ownership', 0, '=', 't'));
                //$crit->add($crit2);
                $crit->setSort('t.priority, t.posted');
                break;

            case XHELP_QRY_STAFF_NEW:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                $crit->add(new \Criteria('ownership', 0, '=', 't'));
                $crit->add(new \Criteria('status', 2, '<', 't'));
                $crit->setSort('t.posted');
                $crit->setOrder('DESC');
                break;

            case XHELP_QRY_STAFF_MINE:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                $crit->add(new \Criteria('ownership', $uid, '=', 't'));
                $crit->add(new \Criteria('status', 2, '<', 't'));
                $crit->setSort('t.posted');
                break;

            case XHELP_QRY_STAFF_ALL:
                $crit->add(new \Criteria('uid', $uid, '=', 'j'));
                break;

            default:
                return 0;
                break;
        }

        return $this->getCountByStaff($crit);
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
            'INSERT INTO `%s` (id, uid, SUBJECT, description, department, priority, STATUS, lastUpdated, ownership, closedBy, totalTimeSpent, posted, userIP, emailHash, email, serverid, overdueTime)
            VALUES (%u, %u, %s, %s, %u, %u, %u, %u, %u, %u, %u, %u, %s, %s, %s, %u, %u)',
            $this->_db->prefix($this->_dbtable),
            $id,
            $uid,
            $this->_db->quoteString($subject),
            $this->_db->quoteString($description),
            $department,
            $priority,
            $status,
            time(),
            $ownership,
            $closedBy,
            $totalTimeSpent,
                       $posted,
            $this->_db->quoteString($userIP),
            $this->_db->quoteString($emailHash),
            $this->_db->quoteString($email),
            $serverid,
            $overdueTime
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
            'UPDATE `%s` SET SUBJECT = %s, description = %s, department = %u, priority = %u, STATUS = %u, lastUpdated = %u, ownership = %u,
            closedBy = %u, totalTimeSpent = %u, userIP = %s, emailHash = %s, email = %s, serverid = %u, overdueTime = %u WHERE id = %u',
            $this->_db->prefix($this->_dbtable),
            $this->_db->quoteString($subject),
            $this->_db->quoteString($description),
            $department,
            $priority,
            $status,
            time(),
            $ownership,
                       $closedBy,
            $totalTimeSpent,
            $this->_db->quoteString($userIP),
            $this->_db->quoteString($emailHash),
            $this->_db->quoteString($email),
            $serverid,
            $overdueTime,
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
        $sql = sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * Create a "select" SQL query
     * @param  \CriteriaElement $criteria {@link CriteriaElement} to match
     * @param bool    $join
     * @param bool    $hasCustFields
     * @return string SQL query
     * @access  private
     */
    public function _selectQuery(\CriteriaElement $criteria = null, $join = false, $hasCustFields = false)
    {
        global $xoopsUser;
        if (!$join) {
            $sql = sprintf('SELECT t.*, (UNIX_TIMESTAMP() - t.posted) AS elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
                            AS lastUpdate  FROM `%s` t INNER JOIN %s s ON t.status = s.id', $this->_db->prefix($this->_dbtable), $this->_db->prefix('xhelp_status'));
        } else {
            if (!$hasCustFields) {
                $sql = sprintf('SELECT t.*, (UNIX_TIMESTAMP() - t.posted) AS elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
                                AS lastUpdate FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s
                                ON t.status = s.id', $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jstaffdept'), $this->_db->prefix('xhelp_status'));
            } else {
                $sql = sprintf('SELECT t.*, (UNIX_TIMESTAMP() - t.posted) AS elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
                                AS lastUpdate FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s
                                ON t.status = s.id INNER JOIN %s f ON t.id = f.ticketid', $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jstaffdept'), $this->_db->prefix('xhelp_status'), $this->_db->prefix('xhelp_ticket_values'));
            }
        }
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }
        $sql = str_replace(XHELP_GLOBAL_UID, $xoopsUser->getVar('uid'), $sql);

        return $sql;
    }

    /**
     * delete a ticket from the database
     *
     * @param \XoopsObject $obj       reference to the {@link Xhelp\Ticket}
     *                                obj to delete
     * @param  bool        $force
     * @return bool FALSE if failed.
     * @access  public
     */
    public function delete(\XoopsObject $obj, $force = false)
    {
        if (0 != strcasecmp($this->classname, get_class($obj))) {
            return false;
        }

        // Remove all ticket responses first
        $hResponses = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        if (!$hResponses->deleteAll(new \Criteria('ticketid', $obj->getVar('id')))) {
            return false;
        }

        // Remove all files associated with this ticket
        $hFiles = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
        if (!$hFiles->deleteAll(new \Criteria('ticketid', $obj->getVar('id')))) {
            return false;
        }

        // Remove custom field values for this ticket
        $hFieldValues = new Xhelp\TicketValuesHandler($GLOBALS['xoopsDB']);
        if (!$hFieldValues->deleteAll(new \Criteria('ticketid', $obj->getVar('id')))) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * increment a value to 1 field for tickets matching a set of conditions
     *
     * @param         $fieldname
     * @param         $fieldvalue
     * @param  \CriteriaElement $criteria {@link CriteriaElement}
     * @return bool FALSE if deletion failed
     * @access  public
     */
    public function incrementAll($fieldname, $fieldvalue, $criteria = null)
    {
        $set_clause = is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldname . '+' . $fieldvalue : $fieldname . ' = ' . $fieldname . '+' . $this->_db->quoteString($fieldvalue);
        $sql        = 'UPDATE ' . $this->_db->prefix($this->_dbtable) . ' SET ' . $set_clause;
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }
}   // end of handler class
