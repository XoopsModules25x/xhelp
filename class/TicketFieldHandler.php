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
 * class TicketFieldHandler
 */
class TicketFieldHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = TicketField::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $dbtable = 'xhelp_ticket_fields';
    public $id      = 'id';

    /**
     * Constructor
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
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
            'INSERT INTO `%s` (id, NAME, description, fieldname, controltype, datatype, required, fieldlength, weight, fieldvalues, defaultvalue, VALIDATION)
            VALUES (%u, %s, %s, %s, %u, %s, %u, %u, %s, %s, %s, %s)',
            $this->db->prefix($this->dbtable),
            $id,
            $this->db->quoteString($name),
            $this->db->quoteString($description),
            $this->db->quoteString($fieldname),
            $controltype,
            $this->db->quoteString($datatype),
            $required,
            $fieldlength,
            $weight,
            $this->db->quoteString($fieldvalues),
            $this->db->quoteString($defaultvalue),
            $this->db->quoteString($validation)
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

        $sql = \sprintf(
            'UPDATE `%s` SET NAME = %s, description = %s, fieldname = %s, controltype = %u, datatype = %s, required = %u, fieldlength = %u, weight = %u, fieldvalues = %s,
            defaultvalue = %s, VALIDATION = %s WHERE id = %u',
            $this->db->prefix($this->dbtable),
            $this->db->quoteString($name),
            $this->db->quoteString($description),
            $this->db->quoteString($fieldname),
            $controltype,
            $this->db->quoteString($datatype),
            $required,
            $fieldlength,
            $weight,
            $this->db->quoteString($fieldvalues),
            $this->db->quoteString($defaultvalue),
            $this->db->quoteString($validation),
            $id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar($this->id));

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @param bool         $force
     * @return bool
     */
    public function insert(\XoopsObject $object, $force = true): bool
    {
        /** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
        $ticketFieldDepartmentHandler = $this->helper->getHandler('TicketFieldDepartment');
        if ($object->isNew()) {
            $add_field = true;
            $fieldname = $object->getVar('fieldname');
        } else {
            $old_obj = $this->get($object->getVar('id'));

            $old_name = $old_obj->getVar('fieldname');
            $new_name = $object->getVar('fieldname');

            $add_field   = false;
            $alter_table = ($old_name != $new_name)
                           || ($old_obj->getVar('fieldlength') != $object->getVar('fieldlength'))
                           || ($old_obj->getVar('controltype') != $object->getVar('controltype'))
                           || ($old_obj->getVar('datatype') != $object->getVar('datatype'));
        }

        //Store base object
        $ret = parent::insert($object, $force);
        if ($ret) {
            //Update Joiner Records
            $ret2 = $ticketFieldDepartmentHandler->removeFieldFromAllDept($object->getVar('id'));

            $depts = $object->getDepartments();

            if (\count($depts)) {
                $ret = $ticketFieldDepartmentHandler->addDepartmentToField($depts, $object->getVar('id'));
            }

            $mysql = $this->mysqlDBType($object);

            if ($add_field) {
                Utility::addDBField('xhelp_ticket_values', $fieldname, $mysql['fieldtype'], $mysql['length']);
            } elseif ($alter_table) {
                Utility::renameDBField('xhelp_ticket_values', $old_name, $new_name, $mysql['fieldtype'], $mysql['length']);
            }
        }

        return $ret;
    }

    /**
     * @param \XoopsObject $object
     * @param bool         $force
     * @return bool
     */
    public function delete(\XoopsObject $object, $force = false): bool
    {
        //Remove FieldDepartment Records
        /** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
        $ticketFieldDepartmentHandler = $this->helper->getHandler('TicketFieldDepartment');
        $fieldId                      = $object->getVar('id');
        if (!$ret = $ticketFieldDepartmentHandler->removeFieldFromAllDept($fieldId, $force)) {
            $object->setErrors('Unable to remove field from departments');
        }

        //Remove values from ticket values table
        if (!$ret = Utility::removeDBField('xhelp_ticket_values', $object->getVar('fieldname'))) {
            $object->setErrors('Unable to remove field from ticket values table');
        }

        //Remove obj from table
        $ret = $this->delete($object, $force);

        return $ret;
    }

    /**
     * @param int $dept
     * @return array
     */
    public function getByDept(int $dept): array
    {
        /** @var TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
        $ticketFieldDepartmentHandler = $this->helper->getHandler('TicketFieldDepartment');
        $ret                          = $ticketFieldDepartmentHandler->fieldsByDepartment($dept);

        return $ret;
    }

    /**
     * @param \XoopsObject $object
     * @return array
     */
    private function mysqlDBType(\XoopsObject $object): array
    {
        $controltype = $object->getVar('controltype');
        $datatype    = $object->getVar('datatype');
        $fieldlength = $object->getVar('fieldlength');

        $mysqldb           = [];
        $mysqldb['length'] = $fieldlength;
        switch ($controltype) {
            case \XHELP_CONTROL_TXTBOX:

                switch ($datatype) {
                    case \_XHELP_DATATYPE_TEXT:
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
                    case \_XHELP_DATATYPE_NUMBER_INT:
                        $mysqldb['fieldtype'] = 'INT';
                        $mysqldb['length']    = 0;
                        break;
                    case \_XHELP_DATATYPE_NUMBER_DEC:
                        $mysqldb['fieldtype'] = 'DECIMAL';
                        $mysqldb['length']    = '7,4';

                    // no break
                    default:
                        $mysqldb['fieldtype'] = 'VARCHAR';
                        $mysqldb['length']    = 255;
                        break;
                }
                break;
            case \XHELP_CONTROL_TXTAREA:
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
            case \XHELP_CONTROL_SELECT:
                switch ($datatype) {
                    case \_XHELP_DATATYPE_TEXT:
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
                    case \_XHELP_DATATYPE_NUMBER_INT:
                        $mysqldb['fieldtype'] = 'INT';
                        $mysqldb['length']    = 0;
                        break;
                    case \_XHELP_DATATYPE_NUMBER_DEC:
                        $mysqldb['fieldtype'] = 'DECIMAL';
                        $mysqldb['length']    = '7,4';

                    // no break
                    default:
                        $mysqldb['fieldtype'] = 'VARCHAR';
                        $mysqldb['length']    = 255;
                        break;
                }
                break;
            case \XHELP_CONTROL_YESNO:
                $mysqldb['fieldtype'] = 'TINYINT';
                $mysqldb['length']    = 1;
                break;
            case \XHELP_CONTROL_RADIOBOX:
                switch ($datatype) {
                    case \_XHELP_DATATYPE_TEXT:
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
                    case \_XHELP_DATATYPE_NUMBER_INT:
                        $mysqldb['fieldtype'] = 'INT';
                        $mysqldb['length']    = 0;
                        break;
                    case \_XHELP_DATATYPE_NUMBER_DEC:
                        $mysqldb['fieldtype'] = 'DECIMAL';
                        $mysqldb['length']    = '7,4';

                    // no break
                    default:
                        $mysqldb['fieldtype'] = 'VARCHAR';
                        $mysqldb['length']    = 255;
                        break;
                }
                break;
            case \XHELP_CONTROL_DATETIME:
                $mysqldb['fieldtype'] = 'INT';
                $mysqldb['length']    = 0;
                break;
            case \XHELP_CONTROL_FILE:
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
