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
    public $_dbtable = 'xhelp_ticket_values';
    public $id       = 'ticketid';
    public $idfield = 'ticketid';

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
     * @param \XoopsObject $obj
     * @return string
     */
    public function insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {     // Assumes cleanVars has already been called
            ${$k} = $v;
        }

        $myFields = $obj->getTicketFields();    // Returns array[$fieldname] = %s or %d for all custom fields

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
                if ('%s' === $datatype) {                      // If this field is a string
                    $sqlVars .= $this->_db->quoteString(${$myField});     // Add text to sqlVars string
                } else {                                    // If this field is a number
                    $sqlVars .= ${$myField};      // Add text to sqlVars string
                }
                ++$count;
            }
        }
        // Create sql statement
        $sql = 'INSERT INTO ' . $this->_db->prefix($this->_dbtable) . ' (' . $sqlFields . ') VALUES (' . $sqlVars . ')';

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $myFields = $obj->getTicketFields();    // Returns array[$fieldname] = %s or %u for all custom fields
        $count    = 1;
        $sqlVars  = '';
        foreach ($myFields as $myField => $datatype) {      // Used to create sql field and value substrings
            if (null !== ${$myField}) {
                if ($count > 1) {                                // If we have been through the loop already
                    $sqlVars .= ', ';
                }
                if ('%s' === $datatype) {                      // If this field is a string
                    $sqlVars .= $myField . ' = ' . $this->_db->quoteString(${$myField});     // Add text to sqlVars string
                } else {                                    // If this field is a number
                    $sqlVars .= $myField . ' = ' . ${$myField};      // Add text to sqlVars string
                }
                ++$count;
            }
        }

        // Create update statement
        $sql = 'UPDATE ' . $this->_db->Prefix($this->_dbtable) . ' SET ' . $sqlVars . ' WHERE ticketid = ' . $obj->getVar('ticketid');

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE ticketid = %u', $this->_db->prefix($this->_dbtable), $obj->getVar($this->id));

        return $sql;
    }
}
