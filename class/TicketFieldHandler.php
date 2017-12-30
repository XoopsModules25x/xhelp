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
 * class TicketFieldHandler
 */
class TicketFieldHandler extends xhelp\BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = TicketField::class;

    /**
     * DB Table Name
     *
     * @var string
     * @access  private
     */
    public $_dbtable = 'xhelp_ticket_fields';
    public $id       = 'id';

    /**
     * Constructor
     *
     * @param \XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db)
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
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf(
            'INSERT INTO %s (id, NAME, description, fieldname, controltype, datatype, required, fieldlength, weight, fieldvalues, defaultvalue, VALIDATION)
            VALUES (%u, %s, %s, %s, %u, %s, %u, %u, %s, %s, %s, %s)',
            $this->_db->prefix($this->_dbtable),
            $id,
            $this->_db->quoteString($name),
            $this->_db->quoteString($description),
            $this->_db->quoteString($fieldname),
            $controltype,
            $this->_db->quoteString($datatype),
            $required,
            $fieldlength,
                       $weight,
            $this->_db->quoteString($fieldvalues),
            $this->_db->quoteString($defaultvalue),
            $this->_db->quoteString($validation)
        );

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

        $sql = sprintf(
            'UPDATE %s SET NAME = %s, description = %s, fieldname = %s, controltype = %u, datatype = %s, required = %u, fieldlength = %u, weight = %u, fieldvalues = %s,
            defaultvalue = %s, VALIDATION = %s WHERE id = %u',
            $this->_db->prefix($this->_dbtable),
            $this->_db->quoteString($name),
            $this->_db->quoteString($description),
            $this->_db->quoteString($fieldname),
            $controltype,
            $this->_db->quoteString($datatype),
            $required,
            $fieldlength,
            $weight,
                       $this->_db->quoteString($fieldvalues),
            $this->_db->quoteString($defaultvalue),
            $this->_db->quoteString($validation),
            $id
        );

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar($this->id));

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @param bool         $force
     * @return bool|void
     */
    public function insert(\XoopsObject $obj, $force = false)
    {
        $hFDept = new xhelp\TicketFieldDepartmentHandler($GLOBALS['xoopsDB']);
        if (!$obj->isNew()) {
            $old_obj = $this->get($obj->getVar('id'));

            $old_name = $old_obj->getVar('fieldname');
            $new_name = $obj->getVar('fieldname');

            $add_field   = false;
            $alter_table = ($old_name != $new_name)
                           || ($old_obj->getVar('fieldlength') != $obj->getVar('fieldlength'))
                           || ($old_obj->getVar('controltype') != $obj->getVar('controltype'))
                           || ($old_obj->getVar('datatype') != $obj->getVar('datatype'));
        } else {
            $add_field = true;
            $fieldname = $obj->getVar('fieldname');
        }

        //Store base object
        if ($ret = parent::insert($obj, $force)) {
            //Update Joiner Records
            $ret2 = $hFDept->removeFieldFromAllDept($obj->getVar('id'));

            $depts = $obj->getDepartments();

            if (count($depts)) {
                $ret = $hFDept->addDepartmentToField($depts, $obj->getVar('id'));
            }

            $mysql = $this->_mysqlDBType($obj);

            if ($add_field) {
                xhelp\Utility::addDBField('xhelp_ticket_values', $fieldname, $mysql['fieldtype'], $mysql['length']);
            } elseif ($alter_table) {
                xhelp\Utility::renameDBField('xhelp_ticket_values', $old_name, $new_name, $mysql['fieldtype'], $mysql['length']);
            }
        }

        return $ret;
    }

    /**
     * @param \XoopsObject $obj
     * @param bool         $force
     * @return bool|RESOURCE
     */
    public function delete(\XoopsObject $obj, $force = false)
    {
        //Remove FieldDepartment Records
        $hFDept = new xhelp\TicketFieldDepartmentHandler($GLOBALS['xoopsDB']);
        if (!$ret = $hFDept->removeFieldFromAllDept($obj, $force)) {
            $obj->setErrors('Unable to remove field from departments');
        }

        //Remove values from ticket values table
        if (!$ret = xhelp\Utility::removeDBField('xhelp_ticket_values', $obj->getVar('fieldname'))) {
            $obj->setErrors('Unable to remove field from ticket values table');
        }

        //Remove obj from table
        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getByDept($dept)
    {
        $hFieldDept = new xhelp\TicketFieldDepartmentHandler($GLOBALS['xoopsDB']);
        $ret        = $hFieldDept->fieldsByDepartment($dept);

        return $ret;
    }

    /**
     * @param $obj
     * @return array
     */
    public function _mysqlDBType($obj)
    {
        $controltype = $obj->getVar('controltype');
        $datatype    = $obj->getVar('datatype');
        $fieldlength = $obj->getVar('fieldlength');

        $mysqldb           = [];
        $mysqldb['length'] = $fieldlength;
        switch ($controltype) {
            case XHELP_CONTROL_TXTBOX:

                switch ($datatype) {
                    case _XHELP_DATATYPE_TEXT:
                        if ($fieldlength <= 255) {
                            $mysqldb['fieldtype'] = 'VARCHAR';
                        } elseif ($fieldlength <= 65535) {
                            $mysqldb['fieldtype'] = 'TEXT';
                        } elseif ($fieldlength <= 16777215) {
                            $mysqldb['fieldtype'] = 'MEDIUMTEXT';
                        } else {
                            $mysqldb['fieldtype'] = 'LONGTEXT';
                        }
                        break;

                    case _XHELP_DATATYPE_NUMBER_INT:
                        $mysqldb['fieldtype'] = 'INT';
                        $mysqldb['length']    = 0;
                        break;

                    case _XHELP_DATATYPE_NUMBER_DEC:
                        $mysqldb['fieldtype'] = 'DECIMAL';
                        $mysqldb['length']    = '7,4';

                        // no break
                    default:
                        $mysqldb['fieldtype'] = 'VARCHAR';
                        $mysqldb['length']    = 255;
                        break;
                }
                break;

            case XHELP_CONTROL_TXTAREA:
                if ($fieldlength <= 255) {
                    $mysqldb['fieldtype'] = 'VARCHAR';
                } elseif ($fieldlength <= 65535) {
                    $mysqldb['fieldtype'] = 'TEXT';
                    $mysqldb['length']    = 0;
                } elseif ($fieldlength <= 16777215) {
                    $mysqldb['fieldtype'] = 'MEDIUMTEXT';
                    $mysqldb['length']    = 0;
                } else {
                    $mysqldb['fieldtype'] = 'LONGTEXT';
                    $mysqldb['length']    = 0;
                }
                break;

            case XHELP_CONTROL_SELECT:
                switch ($datatype) {
                    case _XHELP_DATATYPE_TEXT:
                        if ($fieldlength <= 255) {
                            $mysqldb['fieldtype'] = 'VARCHAR';
                        } elseif ($fieldlength <= 65535) {
                            $mysqldb['fieldtype'] = 'TEXT';
                        } elseif ($fieldlength <= 16777215) {
                            $mysqldb['fieldtype'] = 'MEDIUMTEXT';
                        } else {
                            $mysqldb['fieldtype'] = 'LONGTEXT';
                        }
                        break;

                    case _XHELP_DATATYPE_NUMBER_INT:
                        $mysqldb['fieldtype'] = 'INT';
                        $mysqldb['length']    = 0;
                        break;

                    case _XHELP_DATATYPE_NUMBER_DEC:
                        $mysqldb['fieldtype'] = 'DECIMAL';
                        $mysqldb['length']    = '7,4';

                        // no break
                    default:
                        $mysqldb['fieldtype'] = 'VARCHAR';
                        $mysqldb['length']    = 255;
                        break;
                }
                break;

            case XHELP_CONTROL_YESNO:
                $mysqldb['fieldtype'] = 'TINYINT';
                $mysqldb['length']    = 1;
                break;

            case XHELP_CONTROL_RADIOBOX:
                switch ($datatype) {
                    case _XHELP_DATATYPE_TEXT:
                        if ($fieldlength <= 255) {
                            $mysqldb['fieldtype'] = 'VARCHAR';
                        } elseif ($fieldlength <= 65535) {
                            $mysqldb['fieldtype'] = 'TEXT';
                        } elseif ($fieldlength <= 16777215) {
                            $mysqldb['fieldtype'] = 'MEDIUMTEXT';
                        } else {
                            $mysqldb['fieldtype'] = 'LONGTEXT';
                        }
                        break;

                    case _XHELP_DATATYPE_NUMBER_INT:
                        $mysqldb['fieldtype'] = 'INT';
                        $mysqldb['length']    = 0;
                        break;

                    case _XHELP_DATATYPE_NUMBER_DEC:
                        $mysqldb['fieldtype'] = 'DECIMAL';
                        $mysqldb['length']    = '7,4';

                        // no break
                    default:
                        $mysqldb['fieldtype'] = 'VARCHAR';
                        $mysqldb['length']    = 255;
                        break;
                }
                break;

            case XHELP_CONTROL_DATETIME:
                $mysqldb['fieldtype'] = 'INT';
                $mysqldb['length']    = 0;
                break;

            case XHELP_CONTROL_FILE:
                $mysqldb['fieldtype'] = 'VARCHAR';
                $mysqldb['length']    = 255;
                break;

            default:
                $mysqldb['fieldtype'] = 'VARCHAR';
                $mysqldb['length']    = 255;
                break;
        }

        return $mysqldb;
    }
}
