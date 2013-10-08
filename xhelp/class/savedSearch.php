<?php
//$Id: savedSearch.php,v 1.6 2005/10/10 21:24:56 eric_juden Exp $
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
 * xhelpSavedSearch class
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */
class xhelpSavedSearch extends XoopsObject {
    function xhelpSavedSearch($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('search', XOBJ_DTYPE_OTHER, null, false, 1000000);
        $this->initVar('pagenav_vars', XOBJ_DTYPE_TXTAREA, null, false, 1000000);
        $this->initVar('hasCustFields', XOBJ_DTYPE_INT, 0, false);

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
 * xhelpSavedSearchHandler class
 *
 * SavedSearch Handler for xhelpSavedSearch class
 *
 * @author Eric Juden <ericj@epcusa.com> &
 * @access public
 * @package xhelp
 */

class xhelpSavedSearchHandler extends xhelpBaseObjectHandler {
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpsavedsearch';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    var $_dbtable = 'xhelp_saved_searches';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpSavedSearchHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, uid, name, search, pagenav_vars, hasCustFields) VALUES (%u, %d, %s, %s, %s, %u)",
        $this->_db->prefix($this->_dbtable), $id, $uid, $this->_db->quoteString($name),
        $this->_db->quoteString($search), $this->_db->quoteString($pagenav_vars), $hasCustFields);

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET uid = %d, name = %s, search = %s, pagenav_vars = %s, hasCustFields = %u WHERE id = %u",
        $this->_db->prefix($this->_dbtable), $uid, $this->_db->quoteString($name),
        $this->_db->quoteString($search), $this->_db->quoteString($pagenav_vars), $hasCustFields,
        $id);

        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
        return $sql;
    }

    function getByUid($uid, $has_global = false)
    {
        $uid = intval($uid);
        if($has_global){
            $crit = new CriteriaCompo(new Criteria('uid', $uid), 'OR');
            $crit->add(new Criteria('uid', XHELP_GLOBAL_UID), 'OR');
        } else {
            $crit = new Criteria('uid', $uid);
        }
        $crit->setOrder('ASC');
        $crit->setSort('name');
        $ret = $this->getObjects($crit);
        return $ret;
    }

    function createSQL($crit)
    {
        $sql = $this->_selectQuery($crit);
        return $sql;
    }

    /**
     * delete department matching a set of conditions
     *
     * @param object $criteria {@link CriteriaElement}
     * @return bool FALSE if deletion failed
     * @access	public
     */
    function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM '.$this->_db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }
}
?>