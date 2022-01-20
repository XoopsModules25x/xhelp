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

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * ResponseHandler class
 *
 * Response Handler for Response class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class ResponseHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = Response::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $dbtable = 'xhelp_responses';

    private const TABLE = 'xhelp_responses';
    private const ENTITY = Response::class;
    private const ENTITYNAME = 'Response';
    private const KEYNAME = 'id';
    private const IDENTIFIER = 'id';

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

        $sql = \sprintf(
            'INSERT INTO `%s` (id, uid, ticketid, message, timeSpent, updateTime, userIP, private)
            VALUES (%u, %u, %u, %s, %u, %u, %s, %u)',
            $this->db->prefix($this->dbtable),
            $id,
            $uid,
            $ticketid,
            $this->db->quoteString($message),
            $timeSpent,
            \time(),
            $this->db->quoteString($userIP),
            $private
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
            'UPDATE `%s` SET uid = %u, ticketid = %u, message = %s, timeSpent = %u,
            updateTime = %u, userIP = %s, private = %u WHERE id = %u',
            $this->db->prefix($this->dbtable),
            $uid,
            $ticketid,
            $this->db->quoteString($message),
            $timeSpent,
            \time(),
            $this->db->quoteString($userIP),
            $private,
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
     * delete a response from the database
     *
     * @param \XoopsObject $object    reference to the {@link xhelpResponse}
     *                                obj to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */
    public function delete(\XoopsObject $object, $force = false): bool
    {
        // Remove file associated with this response
        $fileHandler = $this->helper->getHandler('File');
        $criteria    = new \CriteriaCompo(new \Criteria('ticketid', $object->getVar('ticketid')));
        $criteria->add(new \Criteria('responseid', $object->getVar('responseid')));
        if (!$fileHandler->deleteAll($criteria)) {
            return false;
        }

        $ret = parent::delete($object, $force);

        return $ret;
    }

    /**
     * Get number of responses by staff members
     *
     * @param int $ticketid ticket to get count
     * @return int Number of staff responses
     */
    public function getStaffResponseCount(int $ticketid): int
    {
        $sql = \sprintf('SELECT COUNT(*) FROM `%s` r INNER JOIN %s s ON r.uid = s.uid WHERE r.ticketid = %u', $this->db->prefix($this->dbtable), $this->db->prefix('xhelp_staff'), $ticketid);

        $ret = $this->db->query($sql);

        [$count] = $this->db->fetchRow($ret);

        return (int)$count;
    }

    /**
     * Get number of responses by ticketid
     *
     * @param array $tickets where ticketid is key
     * @return bool|array key = ticketid, value = response count
     */
    public function getResponseCounts(array $tickets)
    {
        if (\is_array($tickets)) {
            //$criteria = new \Criteria('ticketid', "(". implode(array_keys($tickets), ',') .")", 'IN');
            $sql = \sprintf('SELECT COUNT(*) AS numresponses, ticketid FROM `%s` WHERE ticketid IN (%s) GROUP BY ticketid', $this->db->prefix($this->dbtable), \implode(',', \array_keys($tickets)));
        } else {
            return false;
        }
        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $tickets[$myrow['ticketid']] = $myrow['numresponses'];
        }

        return $tickets;
    }
}
