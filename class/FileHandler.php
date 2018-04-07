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
 * Xhelp\FileHandler class
 *
 * File Handler for Xhelp\File class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class FileHandler extends Xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = File::class;

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_files';

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

        $sql = sprintf('INSERT INTO `%s` (id, filename, ticketid, responseid, mimetype) VALUES (%u, %s, %u, %d, %s)', $this->_db->prefix($this->_dbtable), $id, $this->_db->quoteString($filename), $ticketid, $responseid, $this->_db->quoteString($mimetype));

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

        $sql = sprintf('UPDATE `%s` SET filename = %s, ticketid = %u, responseid = %d, mimetype = %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $this->_db->quoteString($filename), $ticketid, $responseid, $this->_db->quoteString($mimetype), $id);

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
     * @param \XoopsObject $obj
     * @param bool         $force
     * @return bool
     */
    public function delete(\XoopsObject $obj, $force = false)
    {
        if (!$this->unlinkFile($obj->getFilePath())) {
            return false;
        }
        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * delete file matching a set of conditions
     *
     * @param  \CriteriaElement $criteria {@link CriteriaElement}
     * @return bool   FALSE if deletion failed
     * @access  public
     */
    public function deleteAll($criteria = null)
    {
        $files = $this->getObjects($criteria);
        foreach ($files as $file) {
            $this->unlinkFile($file->getFilePath());
        }

        $sql = 'DELETE FROM ' . $this->_db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->queryF($sql)) {
            return false;
        }

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function unlinkFile($file)
    {
        $ret = false;
        if (is_file($file)) {
            $ret = unlink($file);
        }

        return $ret;
    }
}
