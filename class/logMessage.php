<?php
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

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once XHELP_CLASS_PATH . '/xhelpBaseObjectHandler.php';

/**
 * xhelpLogMessage class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpLogMessage extends XoopsObject
{
    /**
     * XHelpLogMessage constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('lastUpdated', XOBJ_DTYPE_INT, null, true);
        $this->initVar('action', XOBJ_DTYPE_TXTBOX, null, true, 255);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * determine when the log message was updated
     *
     * @return int Timestamp of last update
     * @access  public
     */
    public function lastUpdated()
    {
        return formatTimestamp($this->getVar('lastUpdated'));
    }
}   //end of class

/**
 * xhelpLogMessageHandler class
 *
 * LogMessage Handler for xhelpLogMessage class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class XHelpLogMessageHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelplogmessage';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_logmessages';

    /**
     * Constructor
     *
     * @param object|XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(XoopsDatabase $db)
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

        $sql = sprintf('INSERT INTO %s (id, uid, ticketid, lastUpdated, ACTION) VALUES (%u, %u, %u, %u, %s)', $this->_db->prefix($this->_dbtable), $id, $uid, $ticketid, time(), $this->_db->quoteString($action));

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

        $sql = sprintf('UPDATE %s SET uid = %u, ticketid = %u, lastUpdated = %u, ACTION = %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $ticketid, time(), $this->_db->quoteString($action), $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar($this->_idfield));

        return $sql;
    }
}
