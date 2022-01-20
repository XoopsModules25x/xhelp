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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * Xhelp\TicketField class
 *
 * Metadata that represents a custom field created for xhelp
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class TicketField extends \XoopsObject
{
    public $departments = [];

    /**
     * Class Constructor
     *
     * @param int|array|null $id null for a new object, hash table for an existing object
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', \XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('description', \XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('fieldname', \XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('controltype', \XOBJ_DTYPE_INT, \XHELP_CONTROL_TXTBOX, true);
        $this->initVar('datatype', \XOBJ_DTYPE_TXTBOX, null, true, 64);
        $this->initVar('required', \XOBJ_DTYPE_INT, false, true);
        $this->initVar('fieldlength', \XOBJ_DTYPE_INT, 255, true);
        $this->initVar('weight', \XOBJ_DTYPE_INT, 0, true);
        $this->initVar('fieldvalues', \XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('defaultvalue', \XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('validation', \XOBJ_DTYPE_TXTBOX, null, false);

        if (null !== $id) {
            if (\is_array($id)) {
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
     */
    public function getValues($keys = null, $format = 's', $maxDepth = 1)
    {
        $this->getVar('fieldvalues');
    }

    /**
     * @param Validation\Validator $validator
     */
    public function addValidator(Validation\Validator $validator): void
    {
    }

    /**
     * @param array $val_arr
     */
    public function setValues(array $val_arr): void
    {
        $this->setVar('fieldvalues', $val_arr);
    }

    /**
     * @param array $val_arr
     */
    public function addValues(array $val_arr): void
    {
        if (\is_array($val_arr)) {
            $values = @$this->getVar('fieldvalues');
            if (!\is_array($values)) {
                $values = [];
            }
            foreach ($val_arr as $value => $desc) {
                $values[$value] = $desc;
            }
            $this->setVar('fieldvalues', $values);
        }
    }

    /**
     * @param string $desc
     * @param null   $value
     */
    public function addValue(string $desc, $value = null): void
    {
        //Add value to array
        $values        = $this->getVar('fieldvalues');
        $values[$desc] = $value;
        $this->setVar('fieldvalues', $values);
    }

    /**
     * @param int $dept
     */
    public function addDepartment(int $dept): void
    {
        $dept                     = $dept;
        $this->departments[$dept] = $dept;
    }

    /**
     * @param array $dept_arr
     * @return bool
     */
    public function addDepartments(array $dept_arr): ?bool
    {
        if (!\is_array($dept_arr) || 0 == \count($dept_arr)) {
            return false;
        }
        foreach ($dept_arr as $dept) {
            $dept                     = (int)$dept;
            $this->departments[$dept] = $dept;
        }
        return true;
    }

    /**
     * @param int $dept
     */
    public function removeDepartment(int $dept): void
    {
        $dept                     = $dept;
        $this->departments[$dept] = 0;
    }

    /**
     * @return array
     */
    public function &getDepartments(): array
    {
        return $this->departments;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arr = [];

        $values = $this->getVar('fieldvalues');
        if (\XHELP_CONTROL_YESNO == $this->getVar('controltype')) {
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
            'validation'   => $this->getVar('validation'),
        ];

        return $arr;
    }
}
