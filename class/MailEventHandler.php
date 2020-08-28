<?php

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
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * Xhelp\MailEventHandler class
 *
 * MailEvent Handler for Xhelp\MailEvent class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class MailEventHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = MailEvent::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_mailevent';

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
     * Create a "select" SQL query
     * @param null|\CriteriaElement $criteria {@link CriteriaElement} to match
     * @param null|bool             $join
     * @return string SQL query
     * @access  private
     */
    public function _selectQuery(\CriteriaElement $criteria = null, $join = null)
    {
        if (!$join) {
            $sql = \sprintf('SELECT * FROM `%s`', $this->_db->prefix($this->_dbtable));
        } else {
            $sql = \sprintf('SELECT e.* FROM `%s` e INNER JOIN %s d ON d.id = e.mbox_id', $this->_db->prefix('xhelp_mailevent'), $this->_db->prefix('xhelp_department_mailbox'));
        }

        if (null !== $criteria && $criteria instanceof \CriteriaElement) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . '
                    ' . $criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * retrieve objects from the database
     *
     * @param null $criteria  {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key Should the MailEvent ID be used as array key
     * @return array  array of {@link Xhelp\MailEvent} objects
     * @access  public
     */
    public function &getObjectsJoin($criteria = null, $id_as_key = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->_selectQuery($criteria, true);
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
            $obj = new $this->classname($myrow);
            if (!$id_as_key) {
                $ret[] = $obj;
            } else {
                $ret[$obj->getVar('id')] = $obj;
            }
            unset($obj);
        }

        return $ret;
    }

    /**
     * @param $mbox_id
     * @param $desc
     * @param $class
     * @return bool
     */
    public function newEvent($mbox_id, $desc, $class)
    {
        $event = $this->create();
        $event->setVar('mbox_id', $mbox_id);
        $event->setVar('event_desc', $desc);
        $event->setVar('event_class', $class);
        $event->setVar('posted', \time());

        if (!$this->insert($event, true)) {
            return false;
        }

        return true;
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

        $sql = \sprintf('INSERT INTO `%s` (id, mbox_id, event_desc, event_class, posted) VALUES (%u, %u, %s, %u, %u)', $this->_db->prefix($this->_dbtable), $id, $mbox_id, $this->_db->quoteString($event_desc), $event_class, $posted);

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

        $sql = \sprintf('UPDATE `%s` SET mbox_id = %u, event_desc = %s, event_class = %u, posted = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $mbox_id, $this->_db->quoteString($event_desc), $event_class, $posted, $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }
}
