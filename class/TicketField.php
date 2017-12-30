<?php namespace Xoopsmodules\xhelp;

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

use Xoopsmodules\xhelp;

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * xhelp\TicketField class
 *
 * Metadata that represents a custom field created for xhelp
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class TicketField extends \XoopsObject
{
    public $_departments = [];

    /**
     * Class Constructor
     *
     * @param  mixed $id null for a new object, hash table for an existing object
     * @access public
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('description', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('fieldname', XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('controltype', XOBJ_DTYPE_INT, XHELP_CONTROL_TXTBOX, true);
        $this->initVar('datatype', XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('required', XOBJ_DTYPE_INT, false, true);
        $this->initVar('fieldlength', XOBJ_DTYPE_INT, 255, true);
        $this->initVar('weight', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('fieldvalues', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('defaultvalue', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('validation', XOBJ_DTYPE_TXTBOX, null, false);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * Get the array of possible values for this custom field
     * @param null   $keys
     * @param string $format
     * @param int    $maxDepth
     * @return array A hash table of name/value pairs for the field
     * @access public
     */
    public function getValues($keys = null, $format = 's', $maxDepth = 1)
    {
        $this->getVar('fieldvalues');
    }

    /**
     * @param $validator
     */
    public function addValidator($validator)
    {
    }

    /**
     * @param $val_arr
     */
    public function setValues($val_arr)
    {
        $this->setVar('fieldvalues', $val_arr);
    }

    /**
     * @param $val_arr
     */
    public function addValues($val_arr)
    {
        if (is_array($val_arr)) {
            $values = @$this->getVar('fieldvalues');
            if (!is_array($values)) {
                $values = [];
            }
            foreach ($val_arr as $value => $desc) {
                $values[$value] = $desc;
            }
            $this->setVar('fieldvalues', $values);
        }
    }

    /**
     * @param      $desc
     * @param null $value
     */
    public function addValue($desc, $value = null)
    {
        //Add value to array
        $values        = $this->getVar('fieldvalues');
        $values[$desc] = $value;
        $this->setVar('fieldvalues', $values);
    }

    /**
     * @param $dept
     */
    public function addDepartment($dept)
    {
        $dept                      = (int)$dept;
        $this->_departments[$dept] = $dept;
    }

    /**
     * @param $dept_arr
     * @return bool
     */
    public function addDepartments(&$dept_arr)
    {
        if (!is_array($dept_arr) || 0 == count($dept_arr)) {
            return false;
        }
        foreach ($dept_arr as $dept) {
            $dept                      = (int)$dept;
            $this->_departments[$dept] = $dept;
        }
    }

    /**
     * @param $dept
     */
    public function removeDepartment($dept)
    {
        $dept                      = (int)$dept;
        $this->_departments[$dept] = 0;
    }

    /**
     * @return array
     */
    public function &getDepartments()
    {
        return $this->_departments;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arr = [];

        $values = $this->getVar('fieldvalues');
        if (XHELP_CONTROL_YESNO == $this->getVar('controltype')) {
            $values = [1 => _YES, 0 => _NO];
        }

        $aValues = [];
        foreach ($values as $key => $value) {
            $aValues[] = [$key, $value];
        }

        $arr = [
            'id'           => $this->getVar('id'),
            'name'         => $this->getVar('name'),
            'desc'         => $this->getVar('description'),
            'fieldname'    => $this->getVar('fieldname'),
            'defaultvalue' => $this->getVar('defaultvalue'),
            'currentvalue' => '',
            'controltype'  => $this->getVar('controltype'),
            'required'     => $this->getVar('required'),
            'fieldlength'  => $this->getVar('fieldlength'),
            'weight'       => $this->getVar('weight'),
            'fieldvalues'  => $aValues,
            'datatype'     => $this->getVar('datatype'),
            'validation'   => $this->getVar('validation')
        ];

        return $arr;
    }
}
