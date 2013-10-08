<?php
//$Id: notification.php,v 1.1 2005/03/24 16:53:20 eric_juden Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');


/**
 * xhelpNotification class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpNotification extends XoopsObject {
    function xhelpNotification($id = null)
    {
        $this->initVar('notif_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('staff_setting', XOBJ_DTYPE_INT, null, false);
        $this->initVar('user_setting', XOBJ_DTYPE_INT, null, false);
        $this->initVar('staff_options', XOBJ_DTYPE_ARRAY, null, false, 1000000);

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
 * xhelpNotificationHandler class
 *
 * Notification Handler for xhelpNotification class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpNotificationHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpnotification';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_notifications';
     
    var $_idfield = 'notif_id';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpNotificationHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (notif_id, staff_setting, user_setting, staff_options) VALUES (%u, %u, %u, %s)",
        $this->_db->prefix($this->_dbtable), $notif_id, $staff_setting, $user_setting, $this->_db->quoteString($staff_options));

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET staff_setting = %u, user_setting = %u, staff_options = %s WHERE notif_id = %u",
        $this->_db->prefix($this->_dbtable), $staff_setting, $user_setting, $this->_db->quoteString($staff_options), $notif_id);
        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
        return $sql;
    }
}
?>