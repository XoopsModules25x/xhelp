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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * ResponsesHandler class
 *
 * Response Handler for Responses class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class ResponsesHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = Responses::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_responses';

    /**
     * Constructor
     *
     * @param \XoopsDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::init($db);
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'INSERT INTO `%s` (id, uid, ticketid, message, timeSpent, updateTime, userIP, private)
            VALUES (%u, %u, %u, %s, %u, %u, %s, %u)',
            $this->_db->prefix($this->_dbtable),
            $id,
            $uid,
            $ticketid,
            $this->_db->quoteString($message),
            $timeSpent,
            \time(),
            $this->_db->quoteString($userIP),
            $private
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'UPDATE `%s` SET uid = %u, ticketid = %u, message = %s, timeSpent = %u,
            updateTime = %u, userIP = %s, private = %u WHERE id = %u',
            $this->_db->prefix($this->_dbtable),
            $uid,
            $ticketid,
            $this->_db->quoteString($message),
            $timeSpent,
            \time(),
            $this->_db->quoteString($userIP),
            $private,
            $id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * delete a response from the database
     *
     * @param \XoopsObject $obj       reference to the {@link xhelpResponse}
     *                                obj to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $obj, bool $force = false)
    {
        // Remove file associated with this response
        $fileHandler = new FileHandler($GLOBALS['xoopsDB']);
        $criteria        = new \CriteriaCompo(new \Criteria('ticketid', $obj->getVar('ticketid')));
        $criteria->add(new \Criteria('responseid', $obj->getVar('responseid')));
        if (!$fileHandler->deleteAll($criteria)) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * Get number of responses by staff members
     *
     * @param int $ticketid ticket to get count
     * @return int Number of staff responses
     */
    public function getStaffResponseCount($ticketid): int
    {
        $sql = \sprintf('SELECT COUNT(*) FROM `%s` r INNER JOIN %s s ON r.uid = s.uid WHERE r.ticketid = %u', $this->_db->prefix($this->_dbtable), $this->_db->prefix('xhelp_staff'), $ticketid);

        $ret = $this->_db->query($sql);

        [$count] = $this->_db->fetchRow($ret);

        return $count;
    }

    /**
     * Get number of responses by ticketid
     *
     * @param array $tickets where ticketid is key
     * @return bool|array key = ticketid, value = response count
     */
    public function getResponseCounts($tickets)
    {
        if (\is_array($tickets)) {
            //$criteria = new \Criteria('ticketid', "(". implode(array_keys($tickets), ',') .")", 'IN');
            $sql = \sprintf('SELECT COUNT(*) AS numresponses, ticketid FROM `%s` WHERE ticketid IN (%s) GROUP BY ticketid', $this->_db->prefix($this->_dbtable), \implode(',', \array_keys($tickets)));
        } else {
            return false;
        }
        $result = $this->_db->query($sql);

        if (!$result) {
            return false;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->_db->fetchArray($result))) {
            $tickets[$myrow['ticketid']] = $myrow['numresponses'];
        }

        return $tickets;
    }
}
