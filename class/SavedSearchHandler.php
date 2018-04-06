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
 * Xhelp\SavedSearchHandler class
 *
 * SavedSearch Handler for Xhelp\SavedSearch class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class SavedSearchHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = SavedSearch::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_saved_searches';

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

        $sql = sprintf('INSERT INTO %s (id, uid, NAME, search, pagenav_vars, hasCustFields) VALUES (%u, %d, %s, %s, %s, %u)', $this->_db->prefix($this->_dbtable), $id, $uid, $this->_db->quoteString($name), $this->_db->quoteString($search), $this->_db->quoteString($pagenav_vars), $hasCustFields);

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

        $sql = sprintf('UPDATE %s SET uid = %d, NAME = %s, search = %s, pagenav_vars = %s, hasCustFields = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $this->_db->quoteString($name), $this->_db->quoteString($search), $this->_db->quoteString($pagenav_vars), $hasCustFields, $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * @param      $uid
     * @param bool $has_global
     * @return array
     */
    public function getByUid($uid, $has_global = false)
    {
        $uid = (int)$uid;
        if ($has_global) {
            $crit = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
            $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID), 'OR');
        } else {
            $crit = new \Criteria('uid', $uid);
        }
        $crit->setOrder('ASC');
        $crit->setSort('name');
        $ret = $this->getObjects($crit);

        return $ret;
    }

    /**
     * @param $crit
     * @return string
     */
    public function createSQL($crit)
    {
        $sql = $this->_selectQuery($crit);

        return $sql;
    }

    /**
     * delete department matching a set of conditions
     *
     * @param  \CriteriaElement $criteria {@link CriteriaElement}
     * @return bool   FALSE if deletion failed
     * @access  public
     */
    public function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM ' . $this->_db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }
}
