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
 * TicketListHandler class
 *
 * TicketList Handler for TicketList class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class TicketListHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = TicketList::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_ticket_lists';

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

        $sql = \sprintf('INSERT INTO `%s` (id, uid, searchid, weight) VALUES (%u, %d, %u, %u)', $this->_db->prefix($this->_dbtable), $id, $uid, $searchid, $weight);

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

        $sql = \sprintf('UPDATE `%s` SET uid = %d, searchid = %u, weight = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $searchid, $weight, $id);

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

    // Weight of last ticketList(from staff) and +1

    /**
     * @param $uid
     * @return int
     */
    public function createNewWeight($uid): int
    {
        $uid = (int)$uid;

        $criteria = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
        $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID), 'OR');
        $criteria->setSort('weight');
        $criteria->setOrder('desc');
        $criteria->setLimit(1);
        $ticketList = $this->getObjects($criteria);
        $weight     = (\is_object($ticketList[0]) ? $ticketList[0]->getVar('weight') : 0);

        return $weight + 1;
    }

    /**
     * @param      $listID
     * @param bool $up
     * @return bool
     */
    public function changeWeight($listID, $up = true): ?bool
    {
        $listID           = (int)$listID;
        $ticketList       = $this->get($listID);     // Get ticketList being changed
        $origTicketWeight = $ticketList->getVar('weight');
        $criteria             = new \Criteria('weight', $origTicketWeight, ($up ? '<' : '>'));
        $criteria->setSort('weight');
        $criteria->setOrder($up ? 'DESC' : 'ASC');
        $criteria->setLimit(1);

        $changeTicketList = $this->getObject($criteria);               // Get ticketList being changed with
        $newTicketWeight  = $changeTicketList->getVar('weight');

        $ticketList->setVar('weight', $newTicketWeight);
        if ($this->insert($ticketList, true)) {      // If first one succeeds, change 2nd number
            $changeTicketList->setVar('weight', $origTicketWeight);
            if (!$this->insert($changeTicketList, true)) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param \CriteriaElement|\CriteriaCompo|null $criteria
     * @return array|bool
     */
    public function getObject($criteria = null)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        $id    = $this->idfield;

        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        $numrows = $this->_db->getRowsNum($result);
        if (1 == $numrows) {
            $obj = new $this->classname($this->_db->fetchArray($result));

            return $obj;
        }

        return false;
    }

    /**
     * @param $uid
     * @return array
     */
    public function &getListsByUser($uid): array
    {
        $uid  = (int)$uid;
        $criteria = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
        $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID), 'OR');
        $criteria->setSort('weight');
        $ret = $this->getObjects($criteria);

        return $ret;
    }

    /**
     * @param $uid
     * @return bool
     */
    public function createStaffGlobalLists($uid)
    {
        $ret            = false;
        $hSavedSearches = new SavedSearchHandler($GLOBALS['xoopsDB']);
        $uid            = (int)$uid;

        $criteria = new \Criteria('uid', \XHELP_GLOBAL_UID);
        $criteria->setSort('id');
        $criteria->setOrder('ASC');
        $globalSearches = $hSavedSearches->getObjects($criteria, true);
        $i              = 1;
        foreach ($globalSearches as $search) {
            $list = $this->create();
            $list->setVar('uid', $uid);
            $list->setVar('searchid', $search->getVar('id'));
            $list->setVar('weight', $i);
            $ret = $this->insert($list, true);
            ++$i;
        }

        return $ret;
    }
}
