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

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * ReportParameter class
 *
 * Information about an individual report parameter
 *
 * @author  Eric Juden <eric@3dev.org>
 */
class ReportParameter
{
    public $controltype;
    public $dbaction;
    public $dbfield;
    public $fieldlength;
    public $fieldname;
    public $maxlength;
    public $name;
    public $value;
    public $values;

    /**
     * ReportParameter constructor.
     */
    public function __construct()
    {
        // Contructor
    }

    /**
     * Create a new ReportParameter
     *
     * @return ReportParameter {@link ReportParameter}
     */
    public static function create(): ReportParameter
    {
        $ret = new ReportParameter();

        return $ret;
    }

    /**
     * Add a new report parameter
     *
     * @param int    $controltype
     * @param string $name
     * @param string $fieldname
     * @param string $value
     * @param array  $values
     * @param int    $fieldlength
     * @param string $dbfield
     * @param string $dbaction
     *
     * @return object {@link ReportParameter}
     */
    public static function addParam(int $controltype, string $name, string $fieldname, string $value, array $values, int $fieldlength, string $dbfield, string $dbaction)
    {
        $param              = self::create();
        $param->controltype = $controltype;
        $param->name        = $name;
        $param->fieldname   = $fieldname;
        $param->value       = $value;
        $param->values      = $values;
        $param->fieldlength = $fieldlength;
        $param->maxlength   = (\min($fieldlength, 50));
        $param->dbfield     = $dbfield;
        $param->dbaction    = $dbaction;

        return $param;
    }

    /**
     * Creates the html to display a parameter on the report
     *
     * @param array $vals
     * @return string
     */
    public function displayParam(array $vals = []): ?string
    {
        $controltype = $this->controltype;
        $fieldlength = $this->maxlength;

        if (!empty($vals) && isset($vals[$this->fieldname])) {
            if (\is_array($vals[$this->fieldname])) {
                $this->values = $vals[$this->fieldname][0];
                $this->value  = $vals[$this->fieldname][1];
            } else {
                $this->value = $vals[$this->fieldname];
            }
        }

        switch ($controltype) {
            case \XHELP_CONTROL_TXTBOX:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<input type='text' name='" . $this->fieldname . "' id='" . $this->fieldname . "' value='" . $this->value . "' maxlength='" . $this->maxlength . "' size='" . $this->fieldlength . "'>";
            case \XHELP_CONTROL_TXTAREA:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<textarea name='" . $this->fieldname . "' id='" . $this->fieldname . "' cols='" . $this->fieldlength . "' rows='5'>" . $this->value . '</textarea>';
            case \XHELP_CONTROL_SELECT:
                $ret = "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<select name='" . $this->fieldname . "' id='" . $this->fieldname . "' size='1'>";
                foreach ($this->values as $key => $value) {
                    $ret .= "<option value='" . $key . "' " . (($this->value == $key) ? 'selected' : '') . '>' . $value . '</option>';
                }
                $ret .= '</select>';

                return $ret;
            case \XHELP_CONTROL_MULTISELECT:
                $ret = "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<select name='" . $this->fieldname . "' id='" . $this->fieldname . "' size='3' multiple='multiple'>";
                foreach ($this->values as $key => $value) {
                    $ret .= "<option value='" . $key . "' " . (($this->value == $key) ? 'selected' : '') . '>' . $value . '</option>';
                }
                $ret .= '</select>';

                return $ret;
            case \XHELP_CONTROL_YESNO:
                return "<label for='"
                       . $this->fieldname
                       . "'>"
                       . $this->name
                       . '</label>'
                       . "<input type='radio' name='"
                       . $this->fieldname
                       . "' id='"
                       . $this->fieldname
                       . "1' value='1' "
                       . ((1 == $this->value) ? 'checked' : '')
                       . '>'
                       . \_XHELP_TEXT_YES
                       . "<input type='radio' name='"
                       . $this->fieldname
                       . "' id='"
                       . $this->fieldname
                       . "0' value='0' "
                       . ((1 == $this->value) ? 'checked' : '')
                       . '>'
                       . \_XHELP_TEXT_NO;
            case \XHELP_CONTROL_RADIOBOX:
                $ret = "<label for='" . $this->fieldname . "'>" . $this->name . '</label>';
                foreach ($this->values as $key => $value) {
                    $ret .= "<input type='checkbox' name='" . $this->fieldname . "' id='" . $this->fieldname . "1' value='1' " . (($key == $this->value) ? 'checked' : '') . '>' . $value;
                }

                return $ret;
            case \XHELP_CONTROL_DATETIME:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<input type='text' name='" . $this->fieldname . "' id='" . $this->fieldname . "' value='" . $this->value . "' maxlength='" . $this->maxlength . "' size='" . $this->fieldlength . "'>";
            default:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<input type='text' name='" . $this->fieldname . "' id='" . $this->fieldname . "' value='" . $this->value . "' maxlength='" . $this->maxlength . "' size='" . $this->fieldlength . "'>";
        }
    }
}
