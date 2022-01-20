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
    public $dbtable = 'xhelp_ticket_lists';

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

        $sql = \sprintf('INSERT INTO `%s` (id, uid, searchid, weight) VALUES (%u, %d, %u, %u)', $this->db->prefix($this->dbtable), $id, $uid, $searchid, $weight);

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

        $sql = \sprintf('UPDATE `%s` SET uid = %d, searchid = %u, weight = %u WHERE id = %u', $this->db->prefix($this->dbtable), $uid, $searchid, $weight, $id);

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

    // Weight of last ticketList(from staff) and +1

    /**
     * @param int $uid
     * @return int
     */
    public function createNewWeight(int $uid): int
    {
        $uid = $uid;

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
     * @param int  $listID
     * @param bool $up
     * @return bool
     */
    public function changeWeight(int $listID, bool $up = true): ?bool
    {
        $listID           = $listID;
        $ticketList       = $this->get($listID);     // Get ticketList being changed
        $origTicketWeight = $ticketList->getVar('weight');
        $criteria         = new \Criteria('weight', $origTicketWeight, ($up ? '<' : '>'));
        $criteria->setSort('weight');
        $criteria->setOrder($up ? 'DESC' : 'ASC');
        $criteria->setLimit(1);

        /** @var \XoopsModules\Xhelp\TicketList $changeTicketList */
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
        return true;
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
        $result = $this->db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }
        $numrows = $this->db->getRowsNum($result);
        if (1 == $numrows) {
            $object = new $this->classname($this->db->fetchArray($result));

            return $object;
        }

        return false;
    }

    /**
     * @param int $uid
     * @return array
     */
    public function &getListsByUser(int $uid): array
    {
        $uid      = $uid;
        $criteria = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
        $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID), 'OR');
        $criteria->setSort('weight');
        $ret = $this->getObjects($criteria);

        return $ret;
    }

    /**
     * @param int $uid
     * @return bool
     */
    public function createStaffGlobalLists(int $uid): bool
    {
        $ret            = false;
        $hSavedSearches = $this->helper->getHandler('SavedSearch');
        $uid            = $uid;

        $criteria = new \Criteria('uid', \XHELP_GLOBAL_UID);
        $criteria->setSort('id');
        $criteria->setOrder('ASC');
        $globalSearches = $hSavedSearches->getObjects($criteria, true);
        $i              = 1;
        foreach ($globalSearches as $search) {
            /** @var \XoopsModules\Xhelp\TicketList $list */
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
