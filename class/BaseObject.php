<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

/**
 * class BaseObject
 */
class BaseObject extends \XoopsObject
{
    /**
     * create a new  object
     * @return object {@link Xhelp\BaseObject}
     * @access public
     */
    public function &create()
    {
        return new $this->classname();
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param  int $id ID
     * @return bool <a href='psi_element://Xhelp\Departmentemailserver'>Xhelp\Departmentemailserver</a>
     * @access public
     */
    public function &get($id)
    {
        $id = (int)$id;
        if ($id > 0) {
            $sql = $this->_selectQuery(new \Criteria('id', $id));
            if (!$result = $this->_db->query($sql)) {
                return false;
            }
            $numrows = $this->_db->getRowsNum($result);
            if (1 == $numrows) {
                $obj = new $this->classname($this->_db->fetchArray($result));

                return $obj;
            }
        }

        return false;
    }

    /**
     * Create a "select" SQL query
     * @param  \CriteriaElement $criteria {@link CriteriaElement} to match
     * @return string SQL query
     * @access private
     */
    public function _selectQuery(\CriteriaElement $criteria = null)
    {
        $sql = sprintf('SELECT * FROM %s', $this->_db->prefix($this->_dbtable));
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
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
     * @param  \CriteriaElement $criteria {@link CriteriaElement} to match
     * @return int    count of objects
     * @access public
     */
    public function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->_db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return 0;
        }
        list($count) = $this->_db->fetchRow($result);

        return $count;
    }

    /**
     * delete object based on id
     *
     * @param \XoopsObject $obj
     * @param bool         $force
     * @return int count of objects
     * @internal param object $criteria <a href='psi_element://CriteriaElement'>CriteriaElement</a> to match to match
     * @access   public
     */
    public function delete(\XoopsObject $obj, $force = false)
    {
        if (0 != strcasecmp($this->classname, get_class($obj))) {
            return false;
        }

        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
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
