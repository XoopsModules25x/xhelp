<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;


/**
 * class ConfigOptionHandler
 */
class ConfigOptionHandler extends \XoopsConfigOptionHandler
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
    public $idfield = 'confop_id';

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
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'configoption';

    /**
     * delete configoption matching a set of conditions
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link CriteriaElement}
     * @param bool                                 $force
     * @return bool FALSE if deletion failed
     */
    public function deleteAll($criteria = null, bool $force = false): bool
    {
        $sql = 'DELETE FROM ' . $this->db->prefix($this->_dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$force) {
            if (!$result = $this->db->query($sql)) {
                return false;
            }
        } else {
            if (!$result = $this->db->queryF($sql)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Insert a new option in the database
     *
     * @param \XoopsObject $confoption reference to a {@link XoopsConfigOption}
     * @param bool         $force
     * @return bool TRUE if successfull.
     */
    public function insert(\XoopsObject $confoption, bool $force = false)
    {
        if ('xoopsconfigoption' !== \mb_strtolower(\get_class($confoption))) {
            return false;
        }
        if (!$confoption->isDirty()) {
            return true;
        }
        if (!$confoption->cleanVars()) {
            return false;
        }
        foreach ($confoption->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($confoption->isNew()) {
            $confop_id = $this->db->genId('configoption_confop_id_seq');
            $sql       = \sprintf('INSERT INTO `%s` (confop_id, confop_name, confop_value, conf_id) VALUES (%u, %s, %s, %u)', $this->db->prefix('configoption'), $confop_id, $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $conf_id);
        } else {
            $sql = \sprintf('UPDATE `%s` SET confop_name = %s, confop_value = %s WHERE confop_id = %u', $this->db->prefix('configoption'), $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $confop_id);
        }

        if (!$force) {
            if (!$result = $this->db->query($sql)) {
                return false;
            }
        } else {
            if (!$result = $this->db->queryF($sql)) {
                return false;
            }
        }
        if (empty($confop_id)) {
            $confop_id = $this->db->getInsertId();
        }
        $confoption->assignVar('confop_id', $confop_id);

        return $confop_id;
    }
}
