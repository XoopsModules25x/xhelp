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
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * MailEventHandler class
 *
 * MailEvent Handler for MailEvent class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class MailEventHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = MailEvent::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $dbtable = 'xhelp_mailevent';

    private const TABLE = 'xhelp_mailevent';
    private const ENTITY = MailEvent::class;
    private const ENTITYNAME = 'MailEvent';
    private const KEYNAME = 'id';
    private const IDENTIFIER = 'mbox_id';

    /**
     * Constructor
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
    {
        $this->init($db);
        $this->helper = Helper::getInstance();
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
    }

    /**
     * Create a "select" SQL query
     * @param null|\CriteriaElement $criteria {@link \CriteriaElement} to match
     * @param bool|null             $join
     * @return string SQL query
     */
    public function selectQuery(\CriteriaElement $criteria = null, bool $join = null): string
    {
        if ($join) {
            $sql = \sprintf('SELECT e.* FROM `%s` e INNER JOIN %s d ON d.id = e.mbox_id', $this->db->prefix('xhelp_mailevent'), $this->db->prefix('xhelp_department_mailbox'));
        } else {
            $sql = \sprintf('SELECT * FROM `%s`', $this->db->prefix($this->dbtable));
        }

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
     * retrieve objects from the database
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria  {@link \CriteriaElement} conditions to be met
     * @param bool                                 $id_as_key Should the MailEvent ID be used as array key
     * @return array  array of {@link MailEvent} objects
     */
    public function &getObjectsJoin(\CriteriaElement $criteria = null, bool $id_as_key = false): array
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = $this->selectQuery($criteria, true);
        if (null !== $criteria) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }

        $result = $this->db->query($sql, $limit, $start);
        // If no records from db, return empty array
        if (!$result) {
            return $ret;
        }

        // Add each returned record to the result array
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $object = new $this->classname($myrow);
            if ($id_as_key) {
                $ret[$object->getVar('id')] = $object;
            } else {
                $ret[] = $object;
            }
            unset($object);
        }

        return $ret;
    }

    /**
     * @param int    $mbox_id
     * @param string $desc
     * @param string $class
     * @return bool
     */
    public function newEvent(int $mbox_id, string $desc, string $class): bool
    {
        /** @var \XoopsModules\Xhelp\MailEvent $event */
        $event = $this->create();
        $event->setVar('mbox_id', $mbox_id);
        $event->setVar('event_desc', $desc);
        $event->setVar('event_class', $class);
        $event->setVar('posted', \time());

        if (!$this->insert($event, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf('INSERT INTO `%s` (id, mbox_id, event_desc, event_class, posted) VALUES (%u, %u, %s, %u, %u)', $this->db->prefix($this->dbtable), $id, $mbox_id, $this->db->quoteString($event_desc), $event_class, $posted);

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function updateQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf('UPDATE `%s` SET mbox_id = %u, event_desc = %s, event_class = %u, posted = %u WHERE id = %u', $this->db->prefix($this->dbtable), $mbox_id, $this->db->quoteString($event_desc), $event_class, $posted, $id);

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar('id'));

        return $sql;
    }
}
