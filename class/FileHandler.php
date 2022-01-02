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
 * FileHandler class
 *
 * File Handler for File class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class FileHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = File::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_files';

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

        $sql = \sprintf('INSERT INTO `%s` (id, filename, ticketid, responseid, mimetype) VALUES (%u, %s, %u, %d, %s)', $this->_db->prefix($this->_dbtable), $id, $this->_db->quoteString($filename), $ticketid, $responseid, $this->_db->quoteString($mimetype));

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

        $sql = \sprintf('UPDATE `%s` SET filename = %s, ticketid = %u, responseid = %d, mimetype = %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $this->_db->quoteString($filename), $ticketid, $responseid, $this->_db->quoteString($mimetype), $id);

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
     * @param bool $force
     * @return bool
     */
    public function delete(\XoopsObject $obj, bool $force = false)
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
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement}
     * @return bool   FALSE if deletion failed
     */
    public function deleteAll($criteria = null)
    {
        $files = $this->getObjects($criteria);
        foreach ($files as $file) {
            $this->unlinkFile($file->getFilePath());
        }

        $sql = 'DELETE FROM ' . $this->_db->prefix($this->_dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
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
    public function unlinkFile($file): bool
    {
        $ret = false;
        if (\is_file($file)) {
            $ret = \unlink($file);
        }

        return $ret;
    }
}
