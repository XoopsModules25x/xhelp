<?php
//$Id: ticketValues.php,v 1.11 2005/12/21 14:37:33 ackbarr Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');
xhelpIncludeLang('admin');

/**
 * xhelpTicketValues class
 *
 * Metadata that represents a custom value created for xhelp
 *
 * @author Eric Juden <eric@3dev.org>
 * @access public
 * @package xhelp
 */
class xhelpTicketValues extends XoopsObject
{
    var $_fields = array();
     
    /**
     * Class Constructor
     *
     * @param mixed $ticketid null for a new object, hash table for an existing object
     * @return none
     * @access public
     */
    function xhelpTicketValues($id = null)
    {
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, false);

        $hFields =& xhelpGetHandler('ticketField');
        $fields =& $hFields->getObjects(null, true);

        foreach($fields as $field){
            $key = $field->getVar('fieldname');
            $datatype = $this->_getDataType($field->getVar('datatype'), $field->getVar('controltype'));
            $value = $this->_getValueFromXoopsDataType($datatype);
            $required = $field->getVar('required');
            $maxlength = ($field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50);
            $options = '';

            $this->initVar($key, $datatype, null, $required, $maxlength, $options);

            $this->_fields[$key] = (($field->getVar('datatype') == _XHELP_DATATYPE_TEXT) ? "%s" : "%d");
        }
        $this->_fields['ticketid'] = "%u";


        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    function _getDataType($datatype, $controltype)
    {
        switch($controltype)
        {
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

    function _getXoopsDataType($datatype)
    {
        switch($datatype)
        {
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

    function _getValueFromXoopsDataType($datatype)
    {
        switch($datatype)
        {
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

    function getTicketFields()
    {
        return $this->_fields;
    }

}

class xhelpTicketValuesHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpTicketValues';

    /**
     * DB Table Name
     *
     * @var 		string
     * @access 	private
     */
    var $_dbtable = 'xhelp_ticket_values';
    var $id = 'ticketid';
    var $_idfield = 'ticketid';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpTicketValuesHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {     // Assumes cleanVars has already been called
            ${$k} = $v;
        }

        $myFields = $obj->getTicketFields();    // Returns array[$fieldname] = %s or %d for all custom fields

        $count = 1;
        $sqlFields = "";
        $sqlVars = "";
        foreach($myFields as $myField=>$datatype){      // Create sql name and value pairs
            if(isset(${$myField}) && ${$myField} != null){
                if($count > 1){								// If we have been through the loop already
                    $sqlVars .= ", ";
                    $sqlFields .= ", ";
                }
                $sqlFields .= $myField;
                if($datatype == "%s"){              		// If this field is a string
                    $sqlVars .= $this->_db->quoteString(${$myField});     // Add text to sqlVars string
                } else {                                	// If this field is a number
                    $sqlVars .= ${$myField};      // Add text to sqlVars string
                }
                $count++;
            }
        }
        // Create sql statement
        $sql = "INSERT INTO ". $this->_db->prefix($this->_dbtable)." (" . $sqlFields .") VALUES (". $sqlVars .")";

        return $sql;
    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $myFields = $obj->getTicketFields();    // Returns array[$fieldname] = %s or %u for all custom fields
        $count = 1;
        $sqlVars = "";
        foreach($myFields as $myField=>$datatype){      // Used to create sql field and value substrings
            if(isset(${$myField}) && ${$myField} !== null){
                if($count > 1){								// If we have been through the loop already
                    $sqlVars .= ", ";
                }
                if($datatype == "%s"){              		// If this field is a string
                    $sqlVars .= $myField ." = ". $this->_db->quoteString(${$myField});     // Add text to sqlVars string
                } else {                                	// If this field is a number
                    $sqlVars .= $myField ." = ". ${$myField};      // Add text to sqlVars string
                }
                $count++;
            }
        }

        // Create update statement
        $sql = "UPDATE ". $this->_db->Prefix($this->_dbtable)." SET ". $sqlVars ." WHERE ticketid = ". $obj->getVar('ticketid');
        return $sql;
    }


    function _deleteQuery(&$obj)
    {
        $sql = sprintf("DELETE FROM %s WHERE ticketid = %u", $this->_db->prefix($this->_dbtable), $obj->getVar($this->id));
        return $sql;
    }
}
?>