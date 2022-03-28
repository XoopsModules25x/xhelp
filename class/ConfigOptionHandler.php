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

/**
 * class ConfigOptionHandler
 */
class ConfigOptionHandler extends \XoopsConfigOptionHandler
{
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
    public function init(\XoopsDatabase $db = null): void
    {
        $this->db = $db;
    }

    /**
     * DB table name
     *
     * @var string
     */
    public $dbtable = 'configoption';

    private const TABLE = 'xhelp_ticket_fields';
    private const ENTITY = TicketField::class;
    private const ENTITYNAME = 'TicketField';
    private const KEYNAME = 'id';
    private const IDENTIFIER = 'name';

    /**
     * delete configoption matching a set of conditions
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria {@link CriteriaElement}
     * @param bool                                 $force
     * @return bool FALSE if deletion failed
     */
    public function deleteAll(\CriteriaElement $criteria = null, bool $force = false): bool
    {
        $sql = 'DELETE FROM ' . $this->db->prefix($this->dbtable);
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql .= ' ' . $criteria->renderWhere();
        }
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

    /**
     * Insert a new option in the database
     *
     * @param \XoopsObject $confoption reference to a {@link XoopsConfigOption}
     * @param bool         $force
     * @return bool|int TRUE if successfull.
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
        //TODO mb replace with individual variables
        foreach ($confoption->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($confoption->isNew()) {
            $confop_id = $this->db->genId('configoption_confop_id_seq');
            $sql       = \sprintf('INSERT INTO `%s` (confop_id, confop_name, confop_value, conf_id) VALUES (%u, %s, %s, %u)', $this->db->prefix('configoption'), $confop_id, $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $conf_id);
        } else {
            $sql = \sprintf('UPDATE `%s` SET confop_name = %s, confop_value = %s WHERE confop_id = %u', $this->db->prefix('configoption'), $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $confop_id);
        }

        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        if (empty($confop_id)) {
            $confop_id = $this->db->getInsertId();
        }
        $confoption->assignVar('confop_id', $confop_id);

        return $confop_id;
    }
}
