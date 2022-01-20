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
    public $dbtable = 'xhelp_files';

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
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf('INSERT INTO `%s` (id, filename, ticketid, responseid, mimetype) VALUES (%u, %s, %u, %d, %s)', $this->db->prefix($this->dbtable), $id, $this->db->quoteString($filename), $ticketid, $responseid, $this->db->quoteString($mimetype));

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function updateQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf('UPDATE `%s` SET filename = %s, ticketid = %u, responseid = %d, mimetype = %s WHERE id = %u', $this->db->prefix($this->dbtable), $this->db->quoteString($filename), $ticketid, $responseid, $this->db->quoteString($mimetype), $id);

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar('id'));

        return $sql;
    }

    /**
     * @param \XoopsObject|File $object
     * @param bool              $force
     * @return bool
     */
    public function delete(\XoopsObject $object, $force = false): bool
    {
        if (!$this->unlinkFile($object->getFilePath())) {
            return false;
        }
        $ret = parent::delete($object, $force);

        return $ret;
    }

    /**
     * delete file matching a set of conditions
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement}
     * @return bool   FALSE if deletion failed
     */
    public function deleteAll(\CriteriaElement $criteria = null, $force = true, $asObject = false): bool
    {
        $files = $this->getObjects($criteria);
        foreach ($files as $file) {
            $this->unlinkFile($file->getFilePath());
        }

        $sql = 'DELETE FROM ' . $this->db->prefix($this->dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->queryF($sql)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function unlinkFile(string $file): bool
    {
        $ret = false;
        if (\is_file($file)) {
            $ret = \unlink($file);
        }

        return $ret;
    }
}
