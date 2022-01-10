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
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * xhelpResponseTemplatesHandler class
 *
 * ResponseTemplates Handler for xhelpResponseTemplates class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class ResponseTemplatesHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = ResponseTemplates::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $dbtable = 'xhelp_responsetemplates';

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

        $sql = \sprintf(
            'INSERT INTO `%s` (id, uid, NAME, response)
                VALUES (%u, %u, %s, %s)',
            $this->db->prefix($this->dbtable),
            $id,
            $uid,
            $this->db->quoteString($name),
            $this->db->quoteString($response)
        );

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

        $sql = \sprintf('UPDATE `%s` SET uid = %u, NAME = %s, response = %s WHERE id = %u', $this->db->prefix($this->dbtable), $uid, $this->db->quoteString($name), $this->db->quoteString($response), $id);

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
