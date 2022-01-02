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
 * SavedSearchHandler class
 *
 * SavedSearch Handler for SavedSearch class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class SavedSearchHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = SavedSearch::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_saved_searches';

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

        $sql = \sprintf('INSERT INTO `%s` (id, uid, NAME, search, pagenav_vars, hasCustFields) VALUES (%u, %d, %s, %s, %s, %u)', $this->_db->prefix($this->_dbtable), $id, $uid, $this->_db->quoteString($name), $this->_db->quoteString($search), $this->_db->quoteString($pagenav_vars), $hasCustFields);

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

        $sql = \sprintf('UPDATE `%s` SET uid = %d, NAME = %s, search = %s, pagenav_vars = %s, hasCustFields = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $this->_db->quoteString($name), $this->_db->quoteString($search), $this->_db->quoteString($pagenav_vars), $hasCustFields, $id);

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * @param      $uid
     * @param bool $has_global
     * @return array
     */
    public function getByUid($uid, $has_global = false): array
    {
        $uid = (int)$uid;
        if ($has_global) {
            $criteria = new \CriteriaCompo(new \Criteria('uid', $uid), 'OR');
            $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID), 'OR');
        } else {
            $criteria = new \Criteria('uid', $uid);
        }
        $criteria->setOrder('ASC');
        $criteria->setSort('name');
        $ret = $this->getObjects($criteria);

        return $ret;
    }

    /**
     * @param $criteria
     * @return string
     */
    public function createSQL($criteria): string
    {
        $sql = $this->selectQuery($criteria);

        return $sql;
    }

    /**
     * delete department matching a set of conditions
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement}
     * @return bool   FALSE if deletion failed
     */
    public function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM ' . $this->_db->prefix($this->_dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }
}
