<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;


/**
 * class BaseObject
 */
class BaseObject extends \XoopsObject
{
    /**
     * create a new  object
     * @return object {@link BaseObject}
     */
    public function &create()
    {
        return new $this->classname();
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param int $id ID
     * @return bool <a href='psi_element://Xhelp\Departmentemailserver'>Xhelp\Departmentemailserver</a>
     */
    public function get(int $id): bool
    {
        $id = $id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria('id', (string)$id));
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
     * @param \CriteriaElement|null $criteria {@link CriteriaElement} to match
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null): string
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
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link CriteriaElement} to match
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

        return $count;
    }

    /**
     * delete object based on id
     *
     * @param bool $force
     * @return bool count of objects
     * @internal param object $criteria <a href='psi_element://CriteriaElement'>CriteriaElement</a> to match to match
     */
    public function delete(\XoopsObject $obj, bool $force = false)
    {
        if (0 != \strcasecmp($this->classname, \get_class($obj))) {
            return false;
        }

        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
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
}
