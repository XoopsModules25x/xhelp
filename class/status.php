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
 * xhelpStatus class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpStatus extends XoopsObject
{
    /**
     * XHelpStatus constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('state', XOBJ_DTYPE_INT, null, false);
        $this->initVar('description', XOBJ_DTYPE_TXTBOX, null, false, 50);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}   //end of class

/**
 * xhelpStatusHandler class
 *
 * Status Handler for xhelpStatus class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class XHelpStatusHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelpstatus';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_status';

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

        $sql = sprintf('INSERT INTO %s (id, state, description) VALUES (%u, %u, %s)', $this->_db->prefix($this->_dbtable), $id, $state, $this->_db->quoteString($description));

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

        $sql = sprintf('UPDATE %s SET state = %u, description = %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $state, $this->_db->quoteString($description), $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE ID = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * @param $state
     * @return array
     */
    public function &getStatusesByState($state)
    {
        $aStatuses = [];
        $state     = (int)$state;
        $crit      = new Criteria('state', $state);
        $aStatuses = $this->getObjects($crit, true);

        return $aStatuses;
    }
}
