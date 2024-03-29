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

if (!\defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
// $helper->LoadLanguage('admin');

/**
 * class TicketValuesHandler
 */
class TicketValuesHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = TicketValues::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $dbtable = 'xhelp_ticket_values';
    public $id      = 'ticketid';
    public $idfield = 'ticketid';

    private const TABLE = 'xhelp_ticket_values';
    private const ENTITY = TicketValues::class;
    private const ENTITYNAME = 'TicketValues';
    private const KEYNAME = 'ticketid';
    private const IDENTIFIER = 'ticketid';

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
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {     // Assumes cleanVars has already been called
            ${$k} = $v;
        }

        $myFields = $object->getTicketFields();    // Returns array[$fieldname] = %s or %d for all custom fields

        $count     = 1;
        $sqlFields = '';
        $sqlVars   = '';
        foreach ($myFields as $myField => $datatype) {      // Create sql name and value pairs
            if (null !== ${$myField}) {
                if ($count > 1) {                                // If we have been through the loop already
                    $sqlVars   .= ', ';
                    $sqlFields .= ', ';
                }
                $sqlFields .= $myField;
                if ('%s' === $datatype) {                                 // If this field is a string
                    $sqlVars .= $this->db->quoteString(${$myField});      // Add text to sqlVars string
                } else {                                    // If this field is a number
                    $sqlVars .= ${$myField};                // Add text to sqlVars string
                }
                ++$count;
            }
        }
        // Create sql statement
        $sql = 'INSERT INTO ' . $this->db->prefix($this->dbtable) . ' (' . $sqlFields . ') VALUES (' . $sqlVars . ')';

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

        $myFields = $object->getTicketFields();    // Returns array[$fieldname] = %s or %u for all custom fields
        $count    = 1;
        $sqlVars  = '';
        foreach ($myFields as $myField => $datatype) {      // Used to create sql field and value substrings
            if (null !== ${$myField}) {
                if ($count > 1) {                                // If we have been through the loop already
                    $sqlVars .= ', ';
                }
                if ('%s' === $datatype) {                                                    // If this field is a string
                    $sqlVars .= $myField . ' = ' . $this->db->quoteString(${$myField});      // Add text to sqlVars string
                } else {                                             // If this field is a number
                    $sqlVars .= $myField . ' = ' . ${$myField};      // Add text to sqlVars string
                }
                ++$count;
            }
        }

        // Create update statement
        $sql = 'UPDATE ' . $this->db->prefix($this->dbtable) . ' SET ' . $sqlVars . ' WHERE ticketid = ' . $object->getVar('ticketid');

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE ticketid = %u', $this->db->prefix($this->dbtable), $object->getVar($this->id));

        return $sql;
    }
}
