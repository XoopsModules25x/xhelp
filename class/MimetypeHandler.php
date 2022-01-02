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
 * class MimetypeHandler
 */
class MimetypeHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = Mimetype::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_mimetypes';

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
     * retrieve a mimetype object from the database
     * @param int $id ID of mimetype
     * @return bool <a href='psi_element://Mimetype'>Mimetype</a>
     */
    public function &get($id)
    {
        $ret = false;
        $id  = (int)$id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria('mime_id', (string)$id));
            if (!$result = $this->_db->query($sql)) {
                return $ret;
            }
            $numrows = $this->_db->getRowsNum($result);
            if (1 == $numrows) {
                $obj = new $this->classname($this->_db->fetchArray($result));

                return $obj;
            }
        }

        return $ret;
    }

    /**
     * retrieve objects from the database
     *
     * @param \CriteriaElement|null $criteria {@link \CriteriaElement} conditions to be met
     * @param bool                  $id_as_key
     * @return array array of <a href='psi_element://Mimetype'>Mimetype</a> objects
     *                                        objects
     */
    public function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->_db->fetchArray($result))) {
            $obj   = new $this->classname($myrow);
            $ret[] = $obj;
            unset($obj);
        }

        return $ret;
    }

    /**
     * Format mime_types into array
     *
     * @param null $mime_ext
     * @return array array of mime_types
     */
    public function getArray($mime_ext = null): array
    {
        global $xoopsUser, $xoopsModule, $xhelp_isStaff;

        $ret = [];
        if ($xoopsUser && !$xhelp_isStaff) {
            // For user uploading
            $criteria = new \CriteriaCompo(new \Criteria('mime_user', '1'));   //$sql = sprintf("SELECT * FROM `%s` WHERE mime_user=1", $xoopsDB->prefix('xhelp_mimetypes'));
        } elseif ($xoopsUser && $xhelp_isStaff) {
            // For staff uploading
            $criteria = new \CriteriaCompo(new \Criteria('mime_admin', '1'));  //$sql = sprintf("SELECT * FROM `%s` WHERE mime_admin=1", $xoopsDB->prefix('xhelp_mimetypes'));
        } else {
            return $ret;
        }
        if ($mime_ext) {
            $criteria->add(new \Criteria('mime_ext', $mime_ext));
        }
        $result = $this->getObjects($criteria);

        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        foreach ($result as $mime) {
            $line = \explode(' ', $mime->getVar('mime_types'));
            foreach ($line as $row) {
                $allowed_mimetypes[] = ['type' => $row, 'ext' => $mime->getVar('mime_ext')];
            }
        }

        return $allowed_mimetypes;
    }

    /**
     * Checks to see if the user uploading the file has permissions to upload this mimetype
     * @param file $post_field being uploaded
     * @return false if no permission, return mimetype if has permission
     */
    public function checkMimeTypes($post_field)
    {
        $fname      = $_FILES[$post_field]['name'];
        $farray     = \explode('.', $fname);
        $fextension = \mb_strtolower($farray[\count($farray) - 1]);

        $allowed_mimetypes = $this->getArray();
        if (empty($allowed_mimetypes)) {
            return false;
        }

        foreach ($allowed_mimetypes as $mime) {
            //echo $mime['type'];
            if ($mime['type'] == $_FILES[$post_field]['type']) {
                $allowed_mimetypes = $mime['type'];
                break;
            }

            $allowed_mimetypes = false;
        }

        return $allowed_mimetypes;
    }

    /**
     * Create a "select" SQL query
     * @param \CriteriaElement $criteria {@link \CriteriaElement} to match
     * @param bool             $join
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null, $join = false)
    {
        if (!$join) {
            $sql = \sprintf('SELECT * FROM `%s`', $this->_db->prefix($this->_dbtable));
        } else {
            $sql = \sprintf('SELECT t.* FROM `%s` t INNER JOIN %s j ON t.department = j.department', $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jStaffDept'));
        }
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }

        return $sql;
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

        $sql = \sprintf(
            'INSERT INTO `%s` (mime_id, mime_ext, mime_types, mime_name, mime_admin, mime_user) VALUES
               (%u, %s, %s, %s, %u, %u)',
            $this->_db->prefix($this->_dbtable),
            $mime_id,
            $this->_db->quoteString($mime_ext),
            $this->_db->quoteString($mime_types),
            $this->_db->quoteString($mime_name),
            $mime_admin,
            $mime_user
        );

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

        $sql = \sprintf(
            'UPDATE `%s` SET mime_ext = %s, mime_types = %s, mime_name = %s, mime_admin = %u, mime_user = %u WHERE
               mime_id = %u',
            $this->_db->prefix($this->_dbtable),
            $this->_db->quoteString($mime_ext),
            $this->_db->quoteString($mime_types),
            $this->_db->quoteString($mime_name),
            $mime_admin,
            $mime_user,
            $mime_id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE mime_id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('mime_id'));

        return $sql;
    }
}   // end class
