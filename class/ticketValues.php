<?php
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
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team
 */

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

require_once XHELP_CLASS_PATH . '/xhelpBaseObjectHandler.php';
xhelpIncludeLang('admin');

/**
 * xhelpTicketValues class
 *
 * Metadata that represents a custom value created for xhelp
 *
 * @author  Eric Juden <eric@3dev.org>
 * @access  public
 * @package xhelp
 */
class XHelpTicketValues extends XoopsObject
{
    public $_fields = [];

    /**
     * Class Constructor
     *
     * @param null $id
     * @internal param mixed $ticketid null for a new object, hash table for an existing object
     * @access   public
     */
    public function __construct($id = null)
    {
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, false);

        $hFields = xhelpGetHandler('ticketField');
        $fields  = $hFields->getObjects(null, true);

        foreach ($fields as $field) {
            $key       = $field->getVar('fieldname');
            $datatype  = $this->_getDataType($field->getVar('datatype'), $field->getVar('controltype'));
            $value     = $this->_getValueFromXoopsDataType($datatype);
            $required  = $field->getVar('required');
            $maxlength = ($field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50);
            $options   = '';

            $this->initVar($key, $datatype, null, $required, $maxlength, $options);

            $this->_fields[$key] = ((_XHELP_DATATYPE_TEXT == $field->getVar('datatype')) ? '%s' : '%d');
        }
        $this->_fields['ticketid'] = '%u';

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * @param $datatype
     * @param $controltype
     * @return int
     */
    public function _getDataType($datatype, $controltype)
    {
        switch ($controltype) {
            case XHELP_CONTROL_TXTBOX:
                return $this->_getXoopsDataType($datatype);
                break;

            case XHELP_CONTROL_TXTAREA:
                return $this->_getXoopsDataType($datatype);
                break;

            case XHELP_CONTROL_SELECT:
                return XOBJ_DTYPE_TXTAREA;
                break;

            case XHELP_CONTROL_YESNO:
                return XOBJ_DTYPE_INT;
                break;

            case XHELP_CONTROL_RADIOBOX:
                return XOBJ_DTYPE_TXTBOX;
                break;

            case XHELP_CONTROL_DATETIME:
                return $this->_getXoopsDataType($datatype);
                break;

            case XHELP_CONTROL_FILE:
                return XOBJ_DTYPE_TXTBOX;
                break;

            default:
                return XOBJ_DTYPE_TXTBOX;
                break;
        }
    }

    /**
     * @param $datatype
     * @return int
     */
    public function _getXoopsDataType($datatype)
    {
        switch ($datatype) {
            case _XHELP_DATATYPE_TEXT:
                return XOBJ_DTYPE_TXTBOX;
                break;

            case _XHELP_DATATYPE_NUMBER_INT:
                return XOBJ_DTYPE_INT;
                break;

            case _XHELP_DATATYPE_NUMBER_DEC:
                return XOBJ_DTYPE_OTHER;
                break;

            default:
                return XOBJ_DTYPE_TXTBOX;
                break;
        }
    }

    /**
     * @param $datatype
     * @return float|int|null|string
     */
    public function _getValueFromXoopsDataType($datatype)
    {
        switch ($datatype) {
            case XOBJ_DTYPE_TXTBOX:
            case XOBJ_DTYPE_TXTAREA:
                return '';
                break;

            case XOBJ_DTYPE_INT:
                return 0;
                break;

            case XOBJ_DTYPE_OTHER:
                return 0.0;
                break;

            default:
                return null;
                break;
        }
    }

    /**
     * @return array
     */
    public function getTicketFields()
    {
        return $this->_fields;
    }
}

/**
 * Class XHelpTicketValuesHandler
 */
class XHelpTicketValuesHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelpTicketValues';

    /**
     * DB Table Name
     *
     * @var string
     * @access  private
     */
    public $_dbtable = 'xhelp_ticket_values';
    public $id       = 'ticketid';
    public $_idfield = 'ticketid';

    /**
     * Constructor
     *
     * @param object|XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::init($db);
    }

    /**
     * @param $obj
     * @return string
     */
    public function _insertQuery($obj)
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
            if (isset(${$myField}) && null != ${$myField}) {
                if ($count > 1) {                                // If we have been through the loop already
                    $sqlVars   .= ', ';
                    $sqlFields .= ', ';
                }
                $sqlFields .= $myField;
                if ('%s' == $datatype) {                      // If this field is a string
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
     * @param $obj
     * @return string
     */
    public function _updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $myFields = $obj->getTicketFields();    // Returns array[$fieldname] = %s or %u for all custom fields
        $count    = 1;
        $sqlVars  = '';
        foreach ($myFields as $myField => $datatype) {      // Used to create sql field and value substrings
            if (isset(${$myField}) && null !== ${$myField}) {
                if ($count > 1) {                                // If we have been through the loop already
                    $sqlVars .= ', ';
                }
                if ('%s' == $datatype) {                      // If this field is a string
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
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE ticketid = %u', $this->_db->prefix($this->_dbtable), $obj->getVar($this->id));

        return $sql;
    }
}
