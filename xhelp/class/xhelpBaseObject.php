<?php

Class xhelpBaseObject extends XoopsObject {

    /**
     * create a new  object
     * @return object {@link xhelpBaseObject}
     * @access public
     */
    function &create()
    {
        return new $this->classname();
    }
    /**
     * retrieve an object from the database, based on. use in child classes
     * @param  int    $id ID
     * @return object {@link xhelpdepartmentemailserver}
     * @access public
     */
    function &get($id)
    {
        $id = intval($id);
        if($id > 0) {
            $sql = $this->_selectQuery(new Criteria('id', $id));
            if(!$result = $this->_db->query($sql)) {
                return false;
            }
            $numrows = $this->_db->getRowsNum($result);
            if($numrows == 1) {
                $obj = new $this->classname($this->_db->fetchArray($result));

                return $obj;
            }
        }

        return false;
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
     * @param  object $criteria {@link CriteriaElement} to match
     * @return int    count of objects
     * @access public
     */
    function delete(&$obj, $force = false) {
        if (strcasecmp($this->classname, get_class($obj)) != 0) {
            return false;
        }

        $sql = sprintf("DELETE FROM %s WHERE id = %u", $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
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

}
