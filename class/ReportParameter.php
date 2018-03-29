<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * Xhelp\ReportParameter class
 *
 * Information about an individual report parameter
 *
 * @author  Eric Juden <eric@3dev.org>
 * @access  public
 * @package xhelp
 */
class ReportParameter
{
    public $controltype;
    public $name;
    public $fieldname;
    public $value;
    public $values;
    public $fieldlength;
    public $dbfield;
    public $dbaction;

    /**
     * Xhelp\ReportParameter constructor.
     */
    public function __construct()
    {
        // Contructor
    }

    /**
     * Create a new Xhelp\ReportParameter
     *
     * @return Xhelp\ReportParameter {@link Xhelp\ReportParameter}
     * @access  public
     */
    public static function create()
    {
        $ret = new Xhelp\ReportParameter();

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
     * @return object {@link Xhelp\ReportParameter}
     * @access  public
     */
    public static function addParam($controltype, $name, $fieldname, $value, $values, $fieldlength, $dbfield, $dbaction)
    {
        $param              = self::create();
        $param->controltype = $controltype;
        $param->name        = $name;
        $param->fieldname   = $fieldname;
        $param->value       = $value;
        $param->values      = $values;
        $param->fieldlength = $fieldlength;
        $param->maxlength   = ($fieldlength < 50 ? $fieldlength : 50);
        $param->dbfield     = $dbfield;
        $param->dbaction    = $dbaction;

        return $param;
    }

    /**
     * Creates the html to display a parameter on the report
     *
     * @param array $vals
     * @return string
     * @access  public
     */
    public function displayParam($vals = [])
    {
        $controltype = $this->controltype;
        $fieldlength = $this->maxlength;

        if (!empty($vals) && isset($vals[$this->fieldname])) {
            if (!is_array($vals[$this->fieldname])) {
                $this->value = $vals[$this->fieldname];
            } else {
                $this->values = $vals[$this->fieldname][0];
                $this->value  = $vals[$this->fieldname][1];
            }
        }

        switch ($controltype) {
            case XHELP_CONTROL_TXTBOX:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<input type='text' name='" . $this->fieldname . "' id='" . $this->fieldname . "' value='" . $this->value . "' maxlength='" . $this->maxlength . "' size='" . $this->fieldlength . "'>";
                break;

            case XHELP_CONTROL_TXTAREA:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<textarea name='" . $this->fieldname . "' id='" . $this->fieldname . "' cols='" . $this->fieldlength . "' rows='5'>" . $this->value . '</textarea>';
                break;

            case XHELP_CONTROL_SELECT:
                $ret = "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<select name='" . $this->fieldname . "' id='" . $this->fieldname . "' size='1'>";
                foreach ($this->values as $key => $value) {
                    $ret .= "<option value='" . $key . "' " . (($this->value == $key) ? 'selected' : '') . '>' . $value . '</option>';
                }
                $ret .= '</select>';

                return $ret;
                break;

            case XHELP_CONTROL_MULTISELECT:
                $ret = "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<select name='" . $this->fieldname . "' id='" . $this->fieldname . "' size='3' multiple='multiple'>";
                foreach ($this->values as $key => $value) {
                    $ret .= "<option value='" . $key . "' " . (($this->value == $key) ? 'selected' : '') . '>' . $value . '</option>';
                }
                $ret .= '</select>';

                return $ret;
                break;

            case XHELP_CONTROL_YESNO:
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
                       . _XHELP_TEXT_YES
                       . "<input type='radio' name='"
                       . $this->fieldname
                       . "' id='"
                       . $this->fieldname
                       . "0' value='0' "
                       . ((1 == $this->value) ? 'checked' : '')
                       . '>'
                       . _XHELP_TEXT_NO;
                break;

            case XHELP_CONTROL_RADIOBOX:
                $ret = "<label for='" . $this->fieldname . "'>" . $this->name . '</label>';
                foreach ($this->values as $key => $value) {
                    $ret .= "<input type='checkbox' name='" . $this->fieldname . "' id='" . $this->fieldname . "1' value='1' " . (($key == $this->value) ? 'checked' : '') . '>' . $value;
                }

                return $ret;
                break;

            case XHELP_CONTROL_DATETIME:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<input type='text' name='" . $this->fieldname . "' id='" . $this->fieldname . "' value='" . $this->value . "' maxlength='" . $this->maxlength . "' size='" . $this->fieldlength . "'>";
                break;

            default:
                return "<label for='" . $this->fieldname . "'>" . $this->name . '</label>' . "<input type='text' name='" . $this->fieldname . "' id='" . $this->fieldname . "' value='" . $this->value . "' maxlength='" . $this->maxlength . "' size='" . $this->fieldlength . "'>";
                break;
        }
    }
}
