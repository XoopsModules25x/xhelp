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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
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
    public $dbtable = 'xhelp_mimetypes';

    private const TABLE      = 'xhelp_mimetypes';
    private const ENTITY     = Mimetype::class;
    private const ENTITYNAME = 'Mimetype';
    private const KEYNAME    = 'mime_id';
    private const IDENTIFIER = 'mime_ext';

    /**
     * Constructor
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
    {
        $this->init($db);
        $this->helper = Helper::getInstance();
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * retrieve a mimetype object from the database
     * @param int $id ID of mimetype
     * @return Mimetype|bool
     */
    public function &get($id = null, $fields = null)
    {
        $ret = false;
        $id  = (int)$id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria('mime_id', (string)$id));
            if (!$result = $this->db->query($sql)) {
                return $ret;
            }
            $numrows = $this->db->getRowsNum($result);
            if (1 == $numrows) {
                $object = new $this->classname($this->db->fetchArray($result));

                return $object;
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
    public function &getObjects(\CriteriaElement $criteria = null, $id_as_key = false, $as_object = true): array
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $object = new $this->classname($myrow);
            $ret[]  = $object;
            unset($object);
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

        $ret               = [];
        $allowed_mimetypes = [];
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
     * @param string $post_field being uploaded
     * @return false if no permission, return mimetype if has permission
     */
    public function checkMimeTypes(string $post_field)
    {
        $fname      = $_FILES[$post_field]['name'];
        $farray     = [];
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
            //            $allowed_mimetypes = false;
        }

        return $allowed_mimetypes;
    }

    /**
     * Create a "select" SQL query
     * @param \CriteriaElement|null $criteria {@link \CriteriaElement} to match
     * @param bool                  $join
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null, bool $join = false): string
    {
        if ($join) {
            $sql = \sprintf('SELECT t.* FROM `%s` t INNER JOIN %s j ON t.department = j.department', $this->db->prefix('xhelp_tickets'), $this->db->prefix('xhelp_jStaffDept'));
        } else {
            $sql = \sprintf('SELECT * FROM `%s`', $this->db->prefix($this->dbtable));
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

        $sql = \sprintf(
            'INSERT INTO `%s` (mime_id, mime_ext, mime_types, mime_name, mime_admin, mime_user) VALUES
               (%u, %s, %s, %s, %u, %u)',
            $this->db->prefix($this->dbtable),
            $mime_id,
            $this->db->quoteString($mime_ext),
            $this->db->quoteString($mime_types),
            $this->db->quoteString($mime_name),
            $mime_admin,
            $mime_user
        );

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

        $sql = \sprintf(
            'UPDATE `%s` SET mime_ext = %s, mime_types = %s, mime_name = %s, mime_admin = %u, mime_user = %u WHERE
               mime_id = %u',
            $this->db->prefix($this->dbtable),
            $this->db->quoteString($mime_ext),
            $this->db->quoteString($mime_types),
            $this->db->quoteString($mime_name),
            $mime_admin,
            $mime_user,
            $mime_id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE mime_id = %u', $this->db->prefix($this->dbtable), $object->getVar('mime_id'));

        return $sql;
    }
}   // end class
