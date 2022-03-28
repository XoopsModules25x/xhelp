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
 * @author       Nazar Aziz <nazar@panthersoftware.com>
 * @author       XOOPS Development Team
 */

/**
 * BaseObjectHandler class
 */
class BaseObjectHandler extends \XoopsPersistableObjectHandler
{
    /**
     * Autoincrementing DB fieldname
     * @var string
     */
    public $idfield = 'id';
    public $helper;

    /**
     * Initialization of the handler
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function init(\XoopsMySQLDatabase $db = null): void
    {
        $this->db     = $db;
        $this->helper = Helper::getInstance();
    }

    /**
     * create a new  object
     * @return \XoopsModules\Xhelp\BaseObject|\XoopsObject
     */
//    public function create($isNew = true)
//    {
////        $obj = new $this->classname();
//        $obj = parent::create($isNew);
////        $obj->helper = Helper::getInstance();
//
//        return $obj;
//    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param int $id ID
     * @return mixed object if id exists, false if not
     */
//    public function get($id = null, $fields = null) //$id)
//    {
//        $ret = false;
//        $id  = (int)$id;
//        if ($id > 0) {
//            $sql = $this->selectQuery(new \Criteria($this->idfield, (string)$id));
//            if (!$result = $this->db->query($sql)) {
//                return $ret;
//            }
//            $numrows = $this->db->getRowsNum($result);
//            if (0 == $numrows) {
////                $obj = new $this->classname();
//                $obj = $this->create(true);
//                $obj->setVar('notif_id', $id);
//                return $obj;
//            }
//            if (1 == $numrows) {
//                $objData = $this->db->fetchArray($result);
////                $obj     = new $this->classname($objData);
//                $obj = $this->create($objData);
//
//                return $obj;
//            }
//        }
//
//        return $ret;
//    }

    /**
     * retrieve objects from the database
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria  {@link \CriteriaElement} conditions to be met
     * @param bool                                 $id_as_key Should the department ID be used as array key
     * @return array  array of objects
     */
    //    public function &getObjects($criteria = null, bool $id_as_key = false)
//    public function &getObjects(\CriteriaElement $criteria = null, $id_as_key = false, $as_object = true): array
//    {
//        $ret   = [];
//        $limit = $start = 0;
//        $sql   = $this->selectQuery($criteria);
//        $id    = $this->idfield;
//
//        if (null !== $criteria) {
//            $limit = $criteria->getLimit();
//            $start = $criteria->getStart();
//        }
//
//        $result = $this->db->query($sql, $limit, $start);
//        // If no records from db, return empty array
//        if (!$result || $this->db->getRowsNum($result) < 0) {
//            return $ret;
//        }
//
//        // Add each returned record to the result array
//        while (false !== ($myrow = $this->db->fetchArray($result))) {
//            $obj = new $this->classname($myrow);
////            $obj = $this->create($myrow);
//            if ($id_as_key) {
//                $ret[$obj->getVar($id)] = $obj;
//            } else {
//                $ret[] = $obj;
//            }
//            unset($obj);
//        }
//
//        return $ret;
//    }

    /**
     * @param \XoopsObject $object
     * @param bool         $force
     * @return bool
     */
//    public function insert(\XoopsObject $object, $force = true): bool
//    {
//        // Make sure object is of correct type
//        if (0 != \strcasecmp($this->classname, \get_class($object))) {
//            $object->setErrors('Object is not a ' . $this->classname);
//
//            return false;
//        }
//
//        // Make sure object needs to be stored in DB
//        if (!$object->isDirty()) {
//            $object->setErrors('Object does not need to be saved');
//
//            return true;
//        }
//
//        // Make sure object fields are filled with valid values
//        if (!$object->cleanVars()) {
//            $object->setErrors('Object cannot be sanitized for storage');
//
//            return false;
//        }
//
//        // Create query for DB update
//        if ($object->isNew()) {
//            // Determine next auto-gen ID for table
//            $id  = $this->db->genId($this->db->prefix($this->dbtable) . '_uid_seq');
//            $sql = $this->insertQuery($object);
//        } else {
//            $sql = $this->updateQuery($object);
//        }
//
//        // Update DB
//        if ($force) {
//            $result = $this->db->queryF($sql);
//        } else {
//            $result = $this->db->query($sql);
//        }
//
//        if (!$result) {
//            return false;
//        }
//
//        //Make sure auto-gen ID is stored correctly in object
//        if ($object->isNew()) {
//            $object->assignVar($this->idfield, $this->db->getInsertId());
//        }
//
//        return true;
//    }

    /**
     * Create a "select" SQL query
     * @param \CriteriaElement|null $criteria {@link \CriteriaElement} to match
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null): string
    {
        $sql = \sprintf('SELECT * FROM `%s`', $this->db->prefix($this->dbtable));
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . '
                    ' . $criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * count objects matching a criteria
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement} to match
     * @return int    count of objects
     */
//    public function getCount($criteria = null): int
//    {
//        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($this->dbtable);
//        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
//            $sql .= ' ' . $criteria->renderWhere();
//        }
//        if (!$result = $this->db->query($sql)) {
//            return 0;
//        }
//        [$count] = $this->db->fetchRow($result);
//
//        return (int)$count;
//    }

    /**
     * delete object based on id
     *
     * @param \XoopsObject $object {@link XoopsObject} reference to the object to delete
     * @param bool         $force
     * @return bool        FALSE if failed.
     */
//    public function delete(\XoopsObject $object, $force = false): bool
//    {
//        if (0 != \strcasecmp($this->classname, \get_class($object))) {
//            return false;
//        }
//
//        $sql = $this->deleteQuery($object);
//
//        if ($force) {
//            $result = $this->db->queryF($sql);
//        } else {
//            $result = $this->db->query($sql);
//        }
//        if (!$result) {
//            return false;
//        }
//
//        return true;
//    }

    /**
     * delete department matching a set of conditions
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link \CriteriaElement}
     * @return bool   FALSE if deletion failed
     */
//    public function deleteAll(\CriteriaElement $criteria = null, $force = true, $asObject = false): bool
//    {
//        $sql = 'DELETE FROM ' . $this->db->prefix($this->dbtable);
//        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
//            $sql .= ' ' . $criteria->renderWhere();
//        }
//        if (!$result = $this->db->query($sql)) {
//            return false;
//        }
//
//        return true;
//    }

    /**
     * Assign a value to 1 field for tickets matching a set of conditions
     *
     * @param string         $fieldname
     * @param string|int     $fieldvalue
     * @param \Criteria|null $criteria {@link \CriteriaElement}
     * @return bool FALSE if update failed
     */
    //    public function updateAll(string $fieldname, $fieldvalue, \CriteriaElement $criteria = null): bool
//    public function updateAll($fieldname, $fieldvalue, \CriteriaElement $criteria = null, $force = false): bool
//    {
//        $set_clause = \is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldvalue : $fieldname . ' = ' . $this->db->quoteString($fieldvalue);
//        $sql        = 'UPDATE ' . $this->db->prefix($this->dbtable) . ' SET ' . $set_clause;
//        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
//            $sql .= ' ' . $criteria->renderWhere();
//        }
//        if (!$result = $this->db->query($sql)) {
//            return false;
//        }
//
//        return true;
//    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        return '';
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function updateQuery(\XoopsObject $object): string
    {
        return '';
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        return '';
    }

    /**
     * Singleton - prevent multiple instances of this class
     *
     * @param \XoopsDatabase|null $db
     * @return \XoopsModules\Xhelp\BaseObjectHandler
     */
    public function getInstance(\XoopsDatabase $db = null): BaseObjectHandler
    {
        static $instance;
        if (null === $instance) {
            $classname = $this->classname . 'Handler';
            $instance  = new $classname($db);
        }

        return $instance;
    }
}
