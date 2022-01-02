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

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * TicketEmailsHandler class
 *
 * Department Handler for Department class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class TicketEmailsHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = TicketEmails::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_ticket_submit_emails';

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

        $sql = \sprintf('INSERT INTO `%s` (ticketid, uid, email, suppress) VALUES (%u, %u, %s, %u)', $this->_db->prefix($this->_dbtable), $ticketid, $uid, $this->_db->quoteString($email), $suppress);

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj = null)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE ticketid = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('ticketid'));

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

        $sql = \sprintf('UPDATE `%s` SET suppress = %u WHERE ticketid = %u AND uid = %u AND email = %s', $this->_db->prefix($this->_dbtable), $suppress, $ticketid, $uid, $this->_db->quotestring($email));

        return $sql;
    }

    /**
     * retrieve objects from the database
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria  {@link CriteriaElement} conditions to be met
     * @param bool                                 $id_as_key Should the department ID be used as array key
     * @return array  array of {@link Department} objects
     */
    public function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->_db->fetchArray($result))) {
            $obj                        = new $this->classname($myrow);
            $ret[$obj->getVar('email')] = $obj;
            unset($obj);
        }

        return $ret;
    }

    /**
     * retrieve objects from the database
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link CriteriaElement} conditions to be met
     * @return array array of <a href='psi_element://Department'>Department</a> objects
     *                                                       objects
     * @internal param bool $id_as_key Should the department ID be used as array key
     */
    public function &getObjectsSortedByTicket($criteria = null): array
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->_db->fetchArray($result))) {
            $obj                           = new $this->classname($myrow);
            $ret[$obj->getVar('ticketid')] = $obj;
            unset($obj);
        }

        return $ret;
    }
}
