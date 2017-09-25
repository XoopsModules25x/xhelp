<?php

/**
 * Class XHelpConfigOptionHandler
 */
class XHelpConfigOptionHandler extends XoopsConfigOptionHandler
{
    /**
     * Database connection
     *
     * @var object
     * @access  private
     */
    public $_db;

    /**
     * Autoincrementing DB fieldname
     * @var string
     * @access private
     */
    public $_idfield = 'confop_id';

    /**
     * Constructor
     *
     * @param object|XoopsDatabase $db reference to a xoopsDB object
     */
    public function init(XoopsDatabase $db)
    {
        $this->_db = $db;
    }

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'configoption';

    /**
     * delete configoption matching a set of conditions
     *
     * @param  object $criteria {@link CriteriaElement}
     * @param bool    $force
     * @return bool FALSE if deletion failed
     * @access  public
     */
    public function deleteAll($criteria = null, $force = false)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix($this->_dbtable);
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
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
     * @param  XoopsObject $confoption reference to a {@link XoopsConfigOption}
     * @param bool         $force
     * @return bool TRUE if successfull.
     */
    public function insert(XoopsObject $confoption, $force = false)
    {
        if ('xoopsconfigoption' !== strtolower(get_class($confoption))) {
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
            $sql       = sprintf('INSERT INTO %s (confop_id, confop_name, confop_value, conf_id) VALUES (%u, %s, %s, %u)', $this->db->prefix('configoption'), $confop_id, $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $conf_id);
        } else {
            $sql = sprintf('UPDATE %s SET confop_name = %s, confop_value = %s WHERE confop_id = %u', $this->db->prefix('configoption'), $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $confop_id);
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
