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

/**
 * TicketValues class
 *
 * Metadata that represents a custom value created for xhelp
 *
 * @author  Eric Juden <eric@3dev.org>
 */
class TicketValues extends \XoopsObject
{
    private $fields = [];
    private $helper;

    /**
     * Class Constructor
     *
     * @param int|array|null $id
     * @internal param mixed $ticketid: null for a new object, hash table for an existing object
     */
    public function __construct($id = null)
    {
        $this->initVar('ticketid', \XOBJ_DTYPE_INT, null, false);
        $this->helper = Helper::getInstance();
        $this->helper->loadLanguage('admin');

        /** @var \XoopsModules\Xhelp\TicketFieldHandler $ticketFieldHandler */
        $ticketFieldHandler = $this->helper->getHandler('TicketField');
        $fields             = $ticketFieldHandler->getObjects(null, true);

        foreach ($fields as $field) {
            $key       = $field->getVar('fieldname');
            $datatype  = $this->getDataType($field->getVar('datatype'), $field->getVar('controltype'));
            $value     = $this->getValueFromXoopsDataType($datatype);
            $required  = $field->getVar('required');
            $maxlength = ($field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50);
            $options   = '';

            $this->initVar($key, $datatype, null, $required, $maxlength, $options);

            $this->fields[$key] = ((\_XHELP_DATATYPE_TEXT == $field->getVar('datatype')) ? '%s' : '%d');
        }
        $this->fields['ticketid'] = '%u';

        if (null !== $id) {
            if (\is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * @param string $datatype
     * @param int    $controltype
     * @return int
     */
    private function getDataType(string $datatype, int $controltype): ?int
    {
        switch ($controltype) {
            case \XHELP_CONTROL_TXTBOX:
                return $this->getXoopsDataType($datatype);
                break;
            case \XHELP_CONTROL_TXTAREA:
                return $this->getXoopsDataType($datatype);
                break;
            case \XHELP_CONTROL_SELECT:
                return \XOBJ_DTYPE_TXTAREA;
                break;
            case \XHELP_CONTROL_YESNO:
                return \XOBJ_DTYPE_INT;
                break;
            case \XHELP_CONTROL_RADIOBOX:
                return \XOBJ_DTYPE_TXTBOX;
                break;
            case \XHELP_CONTROL_DATETIME:
                return $this->getXoopsDataType($datatype);
                break;
            case \XHELP_CONTROL_FILE:
                return \XOBJ_DTYPE_TXTBOX;
                break;
            default:
                return \XOBJ_DTYPE_TXTBOX;
                break;
        }
    }

    /**
     * @param string $datatype
     * @return int
     */
    private function getXoopsDataType(string $datatype): ?int
    {
        switch ($datatype) {
            case \_XHELP_DATATYPE_TEXT:
                return \XOBJ_DTYPE_TXTBOX;
                break;
            case \_XHELP_DATATYPE_NUMBER_INT:
                return \XOBJ_DTYPE_INT;
                break;
            case \_XHELP_DATATYPE_NUMBER_DEC:
                return \XOBJ_DTYPE_OTHER;
                break;
            default:
                return \XOBJ_DTYPE_TXTBOX;
                break;
        }
    }

    /**
     * @param int $datatype
     * @return float|int|null|string
     */
    private function getValueFromXoopsDataType(int $datatype)
    {
        switch ($datatype) {
            case \XOBJ_DTYPE_TXTBOX:
            case \XOBJ_DTYPE_TXTAREA:
                return '';
                break;
            case \XOBJ_DTYPE_INT:
                return 0;
                break;
            case \XOBJ_DTYPE_OTHER:
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
    public function getTicketFields(): array
    {
        return $this->fields;
    }
}
