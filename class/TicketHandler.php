<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

use Xmf\Request;
use XoopsModules\Xhelp\RequestsHandler;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
//require_once  \dirname(__DIR__, 3) . '/include/cp_header.php';

global $xoopsUser;

/**
 * class TicketHandler
 */
class TicketHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = Ticket::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $dbtable = 'xhelp_tickets';

    private const TABLE = 'xhelp_tickets';
    private const ENTITY = Ticket::class;
    private const ENTITYNAME = 'Ticket';
    private const KEYNAME = 'id';
    private const IDENTIFIER = 'uid';

    /**
     * Constructor
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
    {
        $this->init($db);
        $this->helper = Helper::getInstance();
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param int $id ID
     * @return mixed object if id exists, false if not
     */
    public function get($id = null, $fields = null)
    {
        $id = (int)$id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria('id', $id, '=', 't'));
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $numrows = $this->db->getRowsNum($result);
            if (1 == $numrows) {
                $object = new $this->classname($this->db->fetchArray($result));

                return $object;
            }
        }

        return false;
    }

    /**
     * find a ticket based on a hash
     *
     * @param string $hash
     * @return Ticket object
     */
    public function getTicketByHash(string $hash): ?Ticket
    {
        $sql = $this->selectQuery(new \Criteria('emailHash', $hash, '=', 't'));
        if (!$result = $this->db->query($sql)) {
            return null;
        }
        $numrows = $this->db->getRowsNum($result);
        if (1 == $numrows) {
            $object = new $this->classname($this->db->fetchArray($result));

            return $object;
        }
    }

    /**
     * Retrieve the list of departments for the specified tickets
     * @param mixed $tickets can be a single value or array consisting of either ticketids or ticket objects
     * @return array array of integers representing the ids of each department
     */
    public function getTicketDepartments($tickets): array
    {
        $a_tickets = [];
        $a_depts   = [];
        if (\is_array($tickets)) {
            foreach ($tickets as $ticket) {
                if (\is_object($ticket)) {
                    $a_tickets[] = $ticket->getVar('id');
                } else {
                    $a_tickets[] = (int)$ticket;
                }
            }
        } else {
            if (\is_object($tickets)) {
                $a_tickets[] = $tickets->getVar('id');
            } else {
                $a_tickets[] = (int)$tickets;
            }
        }

        $sql = \sprintf('SELECT DISTINCT department FROM `%s` WHERE id IN (%s)', $this->db->prefix('xhelp_tickets'), \implode(',', $a_tickets));
        $ret = $this->db->query($sql);

        while (false !== ($temp = $this->db->fetchArray($ret))) {
            $a_depts[] = $temp['department'];
        }

        return $a_depts;
    }

    /**
     * @param \CriteriaElement|\CriteriaCompo $criteria
     * @param bool                            $id_as_key
     * @param bool                            $hasCustFields
     * @return array
     */
    public function &getObjectsByStaff($criteria, bool $id_as_key = false, bool $hasCustFields = false): array
    {
        $sql = $this->selectQuery($criteria, true, $hasCustFields);
        if (\is_object($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $ret = $this->db->query($sql, $limit, $start);
        $arr = [];
        while (false !== ($temp = $this->db->fetchArray($ret))) {
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
     * @param int  $uid
     * @param bool $id_as_key
     * @return array
     */
    public function &getMyUnresolvedTickets(int $uid, bool $id_as_key = false): array
    {
        $uid = $uid;

        // Get all ticketEmail objects where $uid is found
        $ticketEmailsHandler = $this->helper->getHandler('TicketEmails');
        $criteria            = new \Criteria('uid', $uid);
        $ticketEmails        = $ticketEmailsHandler->getObjectsSortedByTicket($criteria);

        // Get friendly array of all ticketids needed
        $aTicketEmails = [];
        foreach ($ticketEmails as $ticketEmail) {
            $aTicketEmails[$ticketEmail->getVar('ticketid')] = $ticketEmail->getVar('ticketid');
        }
        unset($ticketEmails);

        // Get unresolved statuses and filter out the resolved statuses
        $statusHandler = $this->helper->getHandler('Status');
        $criteria      = new \Criteria('state', '1');
        $statuses      = $statusHandler->getObjects($criteria, true);
        $aStatuses     = [];
        foreach ($statuses as $status) {
            $aStatuses[$status->getVar('id')] = $status->getVar('id');
        }
        unset($statuses);

        // Get array of tickets.
        // Only want tickets that are unresolved.
        $criteria = new \CriteriaCompo(new \Criteria('t.id', '(' . \implode(',', \array_keys($aTicketEmails)) . ')', 'IN'));
        $criteria->add(new \Criteria('t.status', '(' . \implode(',', \array_keys($aStatuses)) . ')', 'IN'));
        $tickets = $this->getObjects($criteria, $id_as_key);

        // Return all tickets
        return $tickets;
    }

    /**
     * @param int  $state
     * @param bool $id_as_key
     * @return array
     */
    public function getObjectsByState(int $state, bool $id_as_key = false): array
    {
        $criteria = new \Criteria('state', $state, '=', 's');
        $sql      = $this->selectQuery($criteria, true);
        if (\is_object($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $ret = $this->db->query($sql, $limit, $start);
        $arr = [];
        while (false !== ($temp = $this->db->fetchArray($ret))) {
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
     * @param \CriteriaElement|\CriteriaCompo $criteria
     * @param bool                            $hasCustFields
     * @return int
     */
    public function getCountByStaff($criteria, bool $hasCustFields = false): int
    {
        if ($hasCustFields) {
            $sql = \sprintf(
                'SELECT COUNT(*) AS TicketCount FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s ON t.status = s.id INNER JOIN %s f ON t.id = f.ticketid ',
                $this->db->prefix('xhelp_tickets'),
                $this->db->prefix('xhelp_jstaffdept'),
                $this->db->prefix('xhelp_status'),
                $this->db->prefix('xhelp_ticket_values')
            );
        } else {
            $sql = \sprintf('SELECT COUNT(*) AS TicketCount FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s ON t.status = s.id', $this->db->prefix('xhelp_tickets'), $this->db->prefix('xhelp_jstaffdept'), $this->db->prefix('xhelp_status'));
        }

        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if (!$result = $this->db->query($sql)) {
            return 0;
        }
        [$count] = $this->db->fetchRow($result);

        return (int)$count;
    }

    /**
     * Get all tickets a staff member is in dept
     * @param int    $uid   staff user id
     * @param int    $mode  One of the '_QRY_STAFF_{X}' constants
     * @param int    $start first record to return
     * @param int    $limit number of records to return
     * @param string $sort  Sort Field
     * @param string $order Sort Order
     * @return array  array of {@link Ticket}> objects
     * @todo   Filter by Department, Status
     */
    public function getStaffTickets(int $uid, int $mode = -1, int $start = 0, int $limit = 0, string $sort = '', string $order = ''): array
    {
        $uid      = $uid;
        $arr      = [];
        $criteria = new \CriteriaCompo();
        $criteria->setLimit($limit);
        $criteria->setStart($start);
        switch ($mode) {
            case \XHELP_QRY_STAFF_HIGHPRIORITY:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                $criteria->add(new \Criteria('state', 1, '=', 's'));
                $criteria->add(new \Criteria('ownership', 0, '=', 't'));
                $criteria->setSort('t.priority, t.posted');
                break;
            case \XHELP_QRY_STAFF_NEW:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                $criteria->add(new \Criteria('ownership', 0, '=', 't'));
                $criteria->add(new \Criteria('state', 1, '=', 's'));
                $criteria->setSort('t.posted');
                $criteria->setOrder('DESC');
                break;
            case \XHELP_QRY_STAFF_MINE:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                $criteria->add(new \Criteria('ownership', $uid, '=', 't'));
                $criteria->add(new \Criteria('state', 1, '=', 's'));
                $criteria->setSort('t.posted');
                break;
            case \XHELP_QRY_STAFF_ALL:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                break;
            default:
                return $arr;
        }

        return $this->getObjectsByStaff($criteria);
    }

    /**
     * Get number of tickets based on staff membership
     * @param int $uid staff user id
     * @param int $mode
     * @return int Number of tickets
     * @todo   Filter by Department, Status
     */
    public function getStaffTicketCount(int $uid, int $mode = -1): int
    {
        $criteria = new \CriteriaCompo();
        switch ($mode) {
            case \XHELP_QRY_STAFF_HIGHPRIORITY:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                $criteria->add(new \Criteria('status', 2, '<', 't'));
                $criteria->add(new \Criteria('ownership', 0, '=', 't'));
                //$criteria->add($crit2);
                $criteria->setSort('t.priority, t.posted');
                break;
            case \XHELP_QRY_STAFF_NEW:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                $criteria->add(new \Criteria('ownership', 0, '=', 't'));
                $criteria->add(new \Criteria('status', 2, '<', 't'));
                $criteria->setSort('t.posted');
                $criteria->setOrder('DESC');
                break;
            case \XHELP_QRY_STAFF_MINE:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                $criteria->add(new \Criteria('ownership', $uid, '=', 't'));
                $criteria->add(new \Criteria('status', 2, '<', 't'));
                $criteria->setSort('t.posted');
                break;
            case \XHELP_QRY_STAFF_ALL:
                $criteria->add(new \Criteria('uid', $uid, '=', 'j'));
                break;
            default:
                return 0;
        }

        return $this->getCountByStaff($criteria);
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $totalTimeSpent = Request::getInt('timespent', 0, 'POST');
        $ownership      = Request::getInt('owner', 0, 'POST');

        $sql = \sprintf(
            'INSERT INTO `%s` (uid, SUBJECT, description, department, priority, STATUS, lastUpdated, ownership, closedBy, totalTimeSpent, posted, userIP, emailHash, email, serverid, overdueTime)
            VALUES (%u, %s, %s, %u, %u, %u, %u, %u, %u, %u, %u, %s, %s, %s, %u, %u)',
            $this->db->prefix($this->dbtable),
            //            $id,
            $uid,
            $this->db->quoteString($subject),
            $this->db->quoteString($description),
            $department,
            $priority,
            $status,
            \time(),
            $ownership,
            $closedBy,
            $totalTimeSpent,
            $posted,
            $this->db->quoteString($userIP),
            $this->db->quoteString($emailHash),
            $this->db->quoteString($email),
            $serverid,
            $overdueTime
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function updateQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'UPDATE `%s` SET SUBJECT = %s, description = %s, department = %u, priority = %u, STATUS = %u, lastUpdated = %u, ownership = %u,
            closedBy = %u, totalTimeSpent = %u, userIP = %s, emailHash = %s, email = %s, serverid = %u, overdueTime = %u WHERE id = %u',
            $this->db->prefix($this->dbtable),
            $this->db->quoteString($subject),
            $this->db->quoteString($description),
            $department,
            $priority,
            $status,
            \time(),
            $ownership,
            $closedBy,
            $totalTimeSpent,
            $this->db->quoteString($userIP),
            $this->db->quoteString($emailHash),
            $this->db->quoteString($email),
            $serverid,
            $overdueTime,
            $id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar('id'));

        return $sql;
    }

    /**
     * Create a "select" SQL query
     * @param \CriteriaElement|null $criteria {@link CriteriaElement} to match
     * @param bool                  $join
     * @param bool                  $hasCustFields
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null, bool $join = false, bool $hasCustFields = false): string
    {
        global $xoopsUser;
        if ($join) {
            if ($hasCustFields) {
                $sql = \sprintf(
                    'SELECT t.*, (UNIX_TIMESTAMP() - t.posted) AS elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
                                AS lastUpdate FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s
                                ON t.status = s.id INNER JOIN %s f ON t.id = f.ticketid',
                    $this->db->prefix('xhelp_tickets'),
                    $this->db->prefix('xhelp_jstaffdept'),
                    $this->db->prefix('xhelp_status'),
                    $this->db->prefix('xhelp_ticket_values')
                );
            } else {
                $sql = \sprintf(
                    'SELECT t.*, (UNIX_TIMESTAMP() - t.posted) AS elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
                                AS lastUpdate FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s
                                ON t.status = s.id',
                    $this->db->prefix('xhelp_tickets'),
                    $this->db->prefix('xhelp_jstaffdept'),
                    $this->db->prefix('xhelp_status')
                );
            }
        } else {
            $sql = \sprintf(
                'SELECT t.*, (UNIX_TIMESTAMP() - t.posted) AS elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
                            AS lastUpdate  FROM `%s` t INNER JOIN %s s ON t.status = s.id',
                $this->db->prefix($this->dbtable),
                $this->db->prefix('xhelp_status')
            );
        }
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }
        if (!empty($xoopsUser)) {
            $sql = \str_replace((string)\XHELP_GLOBAL_UID, (string)$xoopsUser->getVar('uid'), $sql);
        }

        return $sql;
    }

    /**
     * delete a ticket from the database
     *
     * @param \XoopsObject $object    reference to the {@link Ticket}
     *                                obj to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $object, $force = false): bool
    {
        if (0 != \strcasecmp($this->classname, \get_class($object))) {
            return false;
        }

        // Remove all ticket responses first
        $responseHandler = $this->helper->getHandler('Response');
        if (!$responseHandler->deleteAll(new \Criteria('ticketid', $object->getVar('id')))) {
            return false;
        }

        // Remove all files associated with this ticket
        $fileHandler = $this->helper->getHandler('File');
        if (!$fileHandler->deleteAll(new \Criteria('ticketid', $object->getVar('id')))) {
            return false;
        }

        // Remove custom field values for this ticket
        $ticketValuesHandler = $this->helper->getHandler('TicketValues');
        if (!$ticketValuesHandler->deleteAll(new \Criteria('ticketid', $object->getVar('id')))) {
            return false;
        }

        $ret = parent::delete($object, $force);

        return $ret;
    }

    /**
     * increment a value to 1 field for tickets matching a set of conditions
     *
     * @param string                               $fieldname
     * @param mixed                                $fieldvalue
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link CriteriaElement}
     * @return bool FALSE if deletion failed
     */
    public function incrementAll(string $fieldname, $fieldvalue, $criteria = null): bool
    {
        $set_clause = \is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldname . '+' . $fieldvalue : $fieldname . ' = ' . $fieldname . '+' . $this->db->quoteString($fieldvalue);
        $sql        = 'UPDATE ' . $this->db->prefix($this->dbtable) . ' SET ' . $set_clause;
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }
}   // end of handler class
