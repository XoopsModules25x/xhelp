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
 * xhelpFile class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpFile extends XoopsObject
{
    /**
     * XHelpFile constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('filename', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('responseid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('mimetype', XOBJ_DTYPE_TXTBOX, null, true, 255);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        $path = XHELP_UPLOAD_PATH . '/' . $this->getVar('filename');

        return $path;
    }

    /**
     * @param     $ticketid
     * @param int $responseid
     * @return bool
     */
    public function rename($ticketid, $responseid = 0)
    {
        $ticketid       = (int)$ticketid;
        $responseid     = (int)$responseid;
        $old_ticketid   = $this->getVar('ticketid');
        $old_responseid = $this->getVar('responseid');

        $filename = $this->getVar('filename');
        if (($old_responseid != 0) && ($responseid != 0)) {   // Was a response and is going to be a response
            $newFilename = str_replace('_' . $old_responseid . '_', '_' . $responseid . '_', $filename);
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_', $newFilename);
        } elseif (($old_responseid != 0) && ($responseid == 0)) { // Was a response and is part of the ticket now
            $newFilename = str_replace('_' . $old_responseid . '_', '_', $filename);
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_', $newFilename);
        } elseif (($old_responseid == 0) && ($responseid != 0)) {  // Was part of the ticket, now going to a response
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_' . $responseid . '_', $filename);
        } elseif (($old_responseid == 0)
                  && ($responseid == 0)) {  // Was part of the ticket, and is part of the ticket now
            $newFilename = str_replace($old_ticketid . '_', $ticketid . '_', $filename);
        }

        $hFile = xhelpGetHandler('file');
        $this->setVar('filename', $newFilename);
        $this->setVar('ticketid', $ticketid);
        $this->setVar('responseid', $responseid);
        if ($hFile->insert($this, true)) {
            $success = true;
        } else {
            $success = false;
        }

        $ret = false;
        if ($success) {
            $ret = $this->renameAtFS($filename, $newFilename);
        }

        return $ret;
    }

    /**
     * @param $oldName
     * @param $newName
     * @return bool
     */
    public function renameAtFS($oldName, $newName)
    {
        $ret = rename(XHELP_UPLOAD_PATH . '/' . $oldName, XHELP_UPLOAD_PATH . '/' . $newName);

        return $ret;
    }
}   //end of class

/**
 * xhelpFileHandler class
 *
 * File Handler for xhelpFile class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpFileHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelpfile';

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

        $sql = sprintf('INSERT INTO %s (id, filename, ticketid, responseid, mimetype) VALUES (%u, %s, %u, %d, %s)', $this->_db->prefix($this->_dbtable), $id, $this->_db->quoteString($filename), $ticketid, $responseid, $this->_db->quoteString($mimetype));

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

        $sql = sprintf('UPDATE %s SET filename = %s, ticketid = %u, responseid = %d, mimetype = %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $this->_db->quoteString($filename), $ticketid, $responseid, $this->_db->quoteString($mimetype), $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * @param XoopsObject $obj
     * @param bool        $force
     * @return bool
     */
    public function delete(XoopsObject $obj, $force = false)
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
     * @param  object $criteria {@link CriteriaElement}
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
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
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
