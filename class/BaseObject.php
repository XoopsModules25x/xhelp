<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * class BaseObject
 */
class BaseObject extends \XoopsObject
{
    /**
     * create a new  object
     * @return BaseObject {@link BaseObject}
     */
    public function &create(): BaseObject
    {
        return new $this->classname();
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param int $id ID
     * @return DepartmentMailBox|bool
     */
    public function get(int $id)
    {
        $id = $id;
        if ($id > 0) {
            $sql = $this->selectQuery(new \Criteria('id', (string)$id));
            if (!$result = $this->db->query($sql)) {
                return false;
            }
            $numrows = $this->db->getRowsNum($result);
            if (1 == $numrows) {
                $obj = new $this->classname($this->db->fetchArray($result));

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
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link CriteriaElement} to match
     * @return int    count of objects
     */
    public function getCount($criteria = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($this->dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return 0;
        }
        [$count] = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * delete object based on id
     *
     * @param \XoopsObject $obj
     * @param bool         $force
     * @return bool true/false on deleting objects
     * @internal param CriteriaElement $criteria  to match
     */
    public function delete(\XoopsObject $obj, bool $force = false): bool
    {
        if (0 != \strcasecmp($this->classname, \get_class($obj))) {
            return false;
        }

        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $obj->getVar('id'));
        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }

        return true;
    }
}
