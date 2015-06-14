<?php

/**
 * xhelpBaseObjectHandler class
 *
 * @author Nazar Aziz <nazar@panthersoftware.com>
 * @access public
 * @package xhelp
 */

Class xhelpBaseObjectHandler extends XoopsObjectHandler {
    /**
     * Database connection
     *
     * @var object
     * @access	private
     */
    var $_db;

    /**
     * Autoincrementing DB fieldname
     * @var string
     * @access private
     */
    var $_idfield = 'id';

    /**
     * Constructor
     *
     * @param object $db reference to a xoopsDB object
     */
    function init(&$db) {
        $this->_db = $db;
    }

    /**
     * create a new  object
     * @return object {@link xhelpBaseObject}
     * @access public
     */
    function &create()
    {
        $obj = new $this->classname();

        return $obj;
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param  int   $id ID
     * @return mixed object if id exists, false if not
     * @access public
     */
    function &get($id)
    {
        $ret = false;
        $id = intval($id);
        if($id > 0) {
            $sql = $this->_selectQuery(new Criteria($this->_idfield, $id));
            if(!$result = $this->_db->query($sql)) {
                return $ret;
            }
            $numrows = $this->_db->getRowsNum($result);
            if($numrows == 1) {
                $obj = new $this->classname($this->_db->fetchArray($result));

                return $obj;
            }
        }

        return $ret;
    }

    /**
     * retrieve objects from the database
     *
     * @param  object $criteria  {@link CriteriaElement} conditions to be met
     * @param  bool   $id_as_key Should the department ID be used as array key
     * @return array  array of objects
     * @access  public
     */
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret    = array();
        $limit  = $start = 0;
        $sql    = $this->_selectQuery($criteria);
        $id     = $this->_idfield;

        if (isset($criteria)) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->_db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while ($myrow = $this->_db->fetchArray($result)) {
            $obj = new $this->classname($myrow);
            if (!$id_as_key) {
                $ret[] =& $obj;
            } else {
                $ret[$obj->getVar($id)] =& $obj;
            }
            unset($obj);
        }

        return $ret;
    }

    function insert(&$obj, $force = false)
    {
        // Make sure object is of correct type
        if (strcasecmp($this->classname, get_class($obj)) != 0) {
            $obj->setErrors('Object is not a ' + $this->classname);

            return false;
        }

        // Make sure object needs to be stored in DB
        if (!$obj->isDirty()) {
            $obj->setErrors('Object does not need to be saved');

            return true;
        }

        // Make sure object fields are filled with valid values
        if (!$obj->cleanVars()) {
            $obj->setErrors('Object cannot be sanitized for storage');

            return false;
        }

        // Create query for DB update
        if ($obj->isNew()) {
            // Determine next auto-gen ID for table
            $id = $this->_db->genId($this->_db->prefix($this->_dbtable).'_uid_seq');
            $sql = $this->_insertQuery($obj);
        } else {
            $sql = $this->_updateQuery($obj);
        }

        // Update DB
        if (false != $force) {
            $result = $this->_db->queryF($sql);
        } else {
            $result = $this->_db->query($sql);
        }

        if (!$result) {
            return false;
        }

        //Make sure auto-gen ID is stored correctly in object
        if ($obj->isNew()) {
            $obj->assignVar($this->_idfield, $this->_db->getInsertId());
        }

        return true;
    }

    /**
     * Create a "select" SQL query
     * @param  object $criteria {@link CriteriaElement} to match
     * @return string SQL query
     * @access private
     */
    function _selectQuery($criteria = null)
    {
        $sql = sprintf('SELECT * FROM %s', $this->_db->prefix($this->_dbtable));
        if(isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' .$criteria->renderWhere();
            if($criteria->getSort() != '') {
                $sql .= ' ORDER BY ' . $criteria->getSort() . '
                    ' .$criteria->getOrder();
            }
        }

        return $sql;
    }

    /**
     * count objects matching a criteria
     *
     * @param  object $criteria {@link CriteriaElement} to match
     * @return int    count of objects
     * @access public
     */
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->_db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result =& $this->_db->query($sql)) {
            return 0;
        }
        list($count) = $this->_db->fetchRow($result);

        return $count;
    }

    /**
     * delete object based on id
     *
     * @param  object $obj   {@link XoopsObject} to delete
     * @param  bool   $force override XOOPS delete protection
     * @return bool   deletion successful?
     * @access public
     */
    function delete(&$obj, $force = false) {
        if (strcasecmp($this->classname, get_class($obj)) != 0) {
            return false;
        }

        $sql = $this->_deleteQuery($obj);

        if (false != $force) {
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
     * @param  object $criteria {@link CriteriaElement}
     * @return bool   FALSE if deletion failed
     * @access	public
     */
    function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM '.$this->_db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Assign a value to 1 field for tickets matching a set of conditions
     *
     * @param  object $criteria {@link CriteriaElement}
     * @return bool   FALSE if update failed
     * @access	public
     */
    function updateAll($fieldname, $fieldvalue, $criteria = null)
    {
        $set_clause = is_numeric($fieldvalue) ? $fieldname.' = '.$fieldvalue : $fieldname.' = '.$this->_db->quoteString($fieldvalue);
        $sql = 'UPDATE '.$this->_db->prefix($this->_dbtable).' SET '.$set_clause;
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }

    function _insertQuery(&$obj) {
        return false;
    }

    function _updateQuery(&$obj) {
        return false;

    }

    function _deleteQuery(&$obj) {
        return false;
    }

    /**
     * Singleton - prevent multiple instances of this class
     *
     * @param  object &$db {@link XoopsHandlerFactory}
     * @return object {@link pagesCategoryHandler}
     * @access public
     */
    function &getInstance(&$db)
    {
        static $instance;
        if(!isset($instance)) {
            $classname = $this->classname.'Handler';
            $instance = new $classname($db);
        }

        return $instance;
    }
}
