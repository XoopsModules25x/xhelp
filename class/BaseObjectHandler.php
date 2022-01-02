<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;


/**
 * BaseObjectHandler class
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 */
class BaseObjectHandler extends \XoopsObjectHandler
{
    /**
     * Database connection
     *
     * @var object
     */
    public $_db;
    /**
     * Autoincrementing DB fieldname
     * @var string
     */
    public $idfield = 'id';

    /**
     * Constructor
     *
     * @param \XoopsDatabase|null $db reference to a xoopsDB object
     */
    public function init(\XoopsDatabase $db = null)
    {
        $this->_db = $db;
    }

    /**
     * create a new  object
     * @return object {@link BaseObject}
     */
    public function create()
    {
        $obj = new $this->classname();

        return $obj;
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param int $int_id ID
     * @return mixed object if id exists, false if not
     */
    public function get($int_id)
    {
        $ret = false;
        $int_id  = (int)$int_id;
        if ($int_id > 0) {
            $sql = $this->selectQuery(new \Criteria($this->idfield, (string)$int_id));
            if (!$result = $this->_db->query($sql)) {
                return $ret;
            }
            $numrows = $this->_db->getRowsNum($result);
            if (0 == $numrows) {
                $obj = new $this->classname();
                $obj->setVar('notif_id', $int_id);
                return $obj;
            }
            if (1 == $numrows) {
                $objData = $this->_db->fetchArray($result);
                $obj = new $this->classname($objData);

                return $obj;
            }
        }

        return $ret;
    }

    /**
     * retrieve objects from the database
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria  {@link \CriteriaElement} conditions to be met
     * @param bool                                 $id_as_key Should the department ID be used as array key
     * @return array  array of objects
     */
    public function &getObjects($criteria = null, bool $id_as_key = false)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria);
        $id    = $this->idfield;

        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result || $this->_db->getRowsNum($result) < 0) {
            return $ret;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->_db->fetchArray($result))) {
            $obj = new $this->classname($myrow);
            if (!$id_as_key) {
                $ret[] = $obj;
            } else {
                $ret[$obj->getVar($id)] = $obj;
            }
            unset($obj);
        }

        return $ret;
    }

    /**
     * @param bool $force
     * @return bool
     */
    public function insert(\XoopsObject $object, bool $force = false)
    {
        // Make sure object is of correct type
        if (0 != \strcasecmp($this->classname, \get_class($object))) {
            $object->setErrors('Object is not a ' . $this->classname);

            return false;
        }

        // Make sure object needs to be stored in DB
        if (!$object->isDirty()) {
            $object->setErrors('Object does not need to be saved');

            return true;
        }

        // Make sure object fields are filled with valid values
        if (!$object->cleanVars()) {
            $object->setErrors('Object cannot be sanitized for storage');

            return false;
        }

        // Create query for DB update
        if ($object->isNew()) {
            // Determine next auto-gen ID for table
            $id  = $this->_db->genId($this->_db->prefix($this->_dbtable) . '_uid_seq');
            $sql = $this->insertQuery($object);
        } else {
            $sql = $this->updateQuery($object);
        }

        // Update DB
        if (false !== $force) {
            $result = $this->_db->queryF($sql);
        } else {
            $result = $this->_db->query($sql);
        }

        if (!$result) {
            return false;
        }

        //Make sure auto-gen ID is stored correctly in object
        if ($object->isNew()) {
            $object->assignVar($this->idfield, $this->_db->getInsertId());
        }

        return true;
    }

    /**
     * Create a "select" SQL query
     * @param \CriteriaElement|null $criteria {@link \CriteriaElement} to match
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null)
    {
        $sql = \sprintf('SELECT * FROM `%s`', $this->_db->prefix($this->_dbtable));
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
    public function getCount($criteria = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->_db->prefix($this->_dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return 0;
        }
        [$count] = $this->_db->fetchRow($result);

        return (int)$count;
    }

    /**
     * delete object based on id
     *
     * @param \XoopsObject $object   {@link XoopsObject} to delete
     * @param bool         $force override XOOPS delete protection
     * @return bool deletion successful?
     */
    public function delete(\XoopsObject $object, bool $force = false)
    {
        if (0 != \strcasecmp($this->classname, \get_class($object))) {
            return false;
        }

        $sql = $this->deleteQuery($object);

        if (false !== $force) {
            $result = $this->_db->queryF($sql);
        } else {
            $result = $this->_db->query($sql);
        }
        if (!$result) {
            return false;
        }

        return true;
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

    /**
     * Assign a value to 1 field for tickets matching a set of conditions
     *
     * @param string         $fieldname
     * @param string|int   $fieldvalue
     * @param \Criteria|null $criteria {@link \CriteriaElement}
     * @return bool FALSE if update failed
     */
    public function updateAll(string $fieldname, $fieldvalue, \CriteriaElement $criteria = null): bool
    {
        $set_clause = \is_numeric($fieldvalue) ? $fieldname . ' = ' . $fieldvalue : $fieldname . ' = ' . $this->_db->quoteString($fieldvalue);
        $sql        = 'UPDATE ' . $this->_db->prefix($this->_dbtable) . ' SET ' . $set_clause;
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * @param \XoopsObject $obj
     * @return bool
     */
    public function insertQuery(\XoopsObject $obj)
    {
        return false;
    }

    /**
     * @param \XoopsObject $obj
     * @return bool|string
     */
    public function updateQuery(\XoopsObject $obj)
    {
        return false;
    }

    /**
     * @param \XoopsObject $obj
     * @return bool
     */
    public function deleteQuery(\XoopsObject $obj)
    {
        return false;
    }

    /**
     * Singleton - prevent multiple instances of this class
     *
     * @return object <a href='psi_element://pagesCategoryHandler'>pagesCategoryHandler</a>
     */
    public function getInstance(\XoopsDatabase $db = null)
    {
        static $instance;
        if (null === $instance) {
            $classname = $this->classname . 'Handler';
            $instance  = new $classname($db);
        }

        return $instance;
    }
}
