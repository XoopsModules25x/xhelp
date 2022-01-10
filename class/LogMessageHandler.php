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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
//
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * LogMessageHandler class
 *
 * LogMessage Handler for LogMessage class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class LogMessageHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = LogMessage::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $dbtable = 'xhelp_logmessages';

    /**
     * Constructor
     *
     * @param \XoopsDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::init($db);
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

        $sql = \sprintf('INSERT INTO `%s` (id, uid, ticketid, lastUpdated, ACTION) VALUES (%u, %u, %u, %u, %s)', $this->db->prefix($this->dbtable), $id, $uid, $ticketid, \time(), $this->db->quoteString($action));

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

        $sql = \sprintf('UPDATE `%s` SET uid = %u, ticketid = %u, lastUpdated = %u, ACTION = %s WHERE id = %u', $this->db->prefix($this->dbtable), $uid, $ticketid, \time(), $this->db->quoteString($action), $id);

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar($this->idfield));

        return $sql;
    }
}
