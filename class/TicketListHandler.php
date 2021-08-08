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


/**
 * Xhelp\TicketListHandler class
 *
 * TicketList Handler for Xhelp\TicketList class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class TicketListHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = TicketList::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_ticket_lists';

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

        $sql = sprintf('INSERT INTO %s (id, uid, searchid, weight) VALUES (%u, %d, %u, %u)', $this->_db->prefix($this->_dbtable), $id, $uid, $searchid, $weight);

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

        $sql = sprintf('UPDATE %s SET uid = %d, searchid = %u, weight = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $searchid, $weight, $id);

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

    // Weight of last ticketList(from staff) and +1

    /**
     * @param $uid
     * @return int
     */
    public function createNewWeight($uid)
    {
        $uid = (int)$uid;

        $crit = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
        $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID), 'OR');
        $crit->setSort('weight');
        $crit->setOrder('desc');
        $crit->setLimit(1);
        $ticketList = $this->getObjects($crit);
        $weight     = (is_object($ticketList[0]) ? $ticketList[0]->getVar('weight') : 0);

        return $weight + 1;
    }

    /**
     * @param      $listID
     * @param bool $up
     * @return bool
     */
    public function changeWeight($listID, $up = true)
    {
        $listID           = (int)$listID;
        $ticketList       = $this->get($listID);     // Get ticketList being changed
        $origTicketWeight = $ticketList->getVar('weight');
        $crit             = new \Criteria('weight', $origTicketWeight, ($up ? '<' : '>'));
        $crit->setSort('weight');
        $crit->setOrder($up ? 'DESC' : 'ASC');
        $crit->setLimit(1);

        $changeTicketList = $this->getObject($crit);               // Get ticketList being changed with
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
     * @param null $criteria
     * @return array|bool
     */
    public function &getObject($criteria = null)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->_selectQuery($criteria);
        $id    = $this->_idfield;

        if (isset($criteria)) {
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
        } else {
            return false;
        }
    }

    /**
     * @param $uid
     * @return array
     */
    public function &getListsByUser($uid)
    {
        $uid  = (int)$uid;
        $crit = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
        $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID), 'OR');
        $crit->setSort('weight');
        $ret = $this->getObjects($crit);

        return $ret;
    }

    /**
     * @param $uid
     * @return bool|void
     */
    public function createStaffGlobalLists($uid)
    {
        $hSavedSearches = new Xhelp\SavedSearchHandler($GLOBALS['xoopsDB']);
        $uid            = (int)$uid;

        $crit = new \Criteria('uid', XHELP_GLOBAL_UID);
        $crit->setSort('id');
        $crit->setOrder('ASC');
        $globalSearches = $hSavedSearches->getObjects($crit, true);
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
