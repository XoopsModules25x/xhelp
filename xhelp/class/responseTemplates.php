<?php
//$Id: responseTemplates.php,v 1.7 2005/11/01 15:05:42 eric_juden Exp $
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
 * xhelpResponseTemplates class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpResponseTemplates extends XoopsObject {
    function xhelpResponseTemplates($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('response', XOBJ_DTYPE_TXTAREA, null, false, 1000000);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}

/**
 * xhelpResponseTemplatesHandler class
 *
 * ResponseTemplates Handler for xhelpResponseTemplates class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpResponseTemplatesHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpresponsetemplates';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_responsetemplates';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpResponseTemplatesHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, uid, name, response)
                VALUES (%u, %u, %s, %s)", $this->_db->prefix($this->_dbtable), $id, $uid, $this->_db->quoteString($name),
        $this->_db->quoteString($response));

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET uid = %u, name = %s, response = %s WHERE id = %u",
        $this->_db->prefix($this->_dbtable), $uid, $this->_db->quoteString($name),
        $this->_db->quoteString($response), $id);

        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
        return $sql;
    }

}
?>