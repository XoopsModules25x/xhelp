<?php namespace XoopsModules\Xhelp;

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

use XoopsModules\Xhelp;

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
$helper->LoadLanguage('admin');

/**
 * Xhelp\TicketValues class
 *
 * Metadata that represents a custom value created for xhelp
 *
 * @author  Eric Juden <eric@3dev.org>
 * @access  public
 * @package xhelp
 */
class TicketValues extends \XoopsObject
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

        $hFields = new Xhelp\TicketFieldHandler($GLOBALS['xoopsDB']);
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
