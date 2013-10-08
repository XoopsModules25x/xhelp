<?php
// $Id: reportParameter.php,v 1.5 2006/02/06 19:37:59 eric_juden Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * xhelpReportParameter class
 *
 * Information about an individual report parameter
 *
 * @author Eric Juden <eric@3dev.org>
 * @access public
 * @package xhelp
 */
class xhelpReportParameter{
    var $controltype;
    var $name;
    var $fieldname;
    var $value;
    var $values;
    var $fieldlength;
    var $dbfield;
    var $dbaction;

    function xhelpReportParameter()
    {
        // Contructor
    }

    /**
     * Create a new xhelpReportParameter
     *
     * @return object {@link xhelpReportParameter}
     * @access	public
     */
    function &create()
    {
        $ret = new xhelpReportParameter();
        return $ret;
    }

    /**
     * Add a new report parameter
     *
     * @param int $controltype
     * @param string $name
     * @param string $fieldname
     * @param string $value
     * @param array $values
     * @param int $fieldlength
     * @param string $dbfield
     * @param string $dbaction
     *
     * @return object {@link xhelpReportParameter}
     * @access	public
     */
    function addParam($controltype, $name, $fieldname, $value, $values, $fieldlength, $dbfield, $dbaction)
    {
        $param =& xhelpReportParameter::create();
        $param->controltype = $controltype;
        $param->name = $name;
        $param->fieldname = $fieldname;
        $param->value = $value;
        $param->values = $values;
        $param->fieldlength = $fieldlength;
        $param->maxlength = (($fieldlength) < 50 ? $fieldlength : 50);
        $param->dbfield = $dbfield;
        $param->dbaction = $dbaction;

        return $param;
    }

    /**
     * Creates the html to display a parameter on the report
     *
     * @return string
     * @access	public
     */
    function displayParam($vals = array())
    {
        $controltype = $this->controltype;
        $fieldlength = $this->maxlength;

        if(!empty($vals) && isset($vals[$this->fieldname])){
            if(!is_array($vals[$this->fieldname])){
                $this->value = $vals[$this->fieldname];
            } else {
                $this->values = $vals[$this->fieldname][0];
                $this->value = $vals[$this->fieldname][1];
            }
        }

        switch ($controltype)
        {
            case XHELP_CONTROL_TXTBOX:
                return "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<input type='text' name='".$this->fieldname."' id='".$this->fieldname."' value='".$this->value."' maxlength='".$this->maxlength."' size='".$this->fieldlength."' />";
                break;

            case XHELP_CONTROL_TXTAREA:
                return "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<textarea name='".$this->fieldname."' id='".$this->fieldname."' cols='".$this->fieldlength."' rows='5'>".$this->value."</textarea>";
                break;

            case XHELP_CONTROL_SELECT:
                $ret = "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<select name='".$this->fieldname."' id='".$this->fieldname."' size='1'>";
                foreach($this->values as $key=>$value){
                    $ret .= "<option value='".$key."' ". (($this->value == $key) ? "selected='selected'" : "").">".$value."</option>";
                }
                $ret .= "</select>";
                return $ret;
                break;

            case XHELP_CONTROL_MULTISELECT:
                $ret = "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<select name='".$this->fieldname."' id='".$this->fieldname."' size='3' multiple='multiple'>";
                foreach($this->values as $key=>$value){
                    $ret .= "<option value='".$key."' ". (($this->value == $key) ? "selected='selected'" : "").">".$value."</option>";
                }
                $ret .= "</select>";
                return $ret;
                break;

            case XHELP_CONTROL_YESNO:
                return "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<input type='radio' name='".$this->fieldname."' id='".$this->fieldname."1' value='1' ".(($this->value == 1) ? "checked='checked'" : "") ." />"._XHELP_TEXT_YES .
                       "<input type='radio' name='".$this->fieldname."' id='".$this->fieldname."0' value='0' ".(($this->value == 1) ? "checked='checked'" : "") ." />"._XHELP_TEXT_NO;
                break;

            case XHELP_CONTROL_RADIOBOX:
                $ret = "<label for='".$this->fieldname."'>".$this->name."</label>";
                foreach($this->values as $key=>$value){
                    $ret .= "<input type='checkbox' name='".$this->fieldname."' id='".$this->fieldname."1' value='1' ".(($key == $this->value) ? "checked='checked'" : "") ." />".$value;
                }
                return $ret;
                break;

            case XHELP_CONTROL_DATETIME:
                return "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<input type='text' name='".$this->fieldname."' id='".$this->fieldname."' value='".$this->value."' maxlength='".$this->maxlength."' size='".$this->fieldlength."' />";
                break;

            default:
                return "<label for='".$this->fieldname."'>".$this->name."</label>".
                       "<input type='text' name='".$this->fieldname."' id='".$this->fieldname."' value='".$this->value."' maxlength='".$this->maxlength."' size='".$this->fieldlength."' />";
                break;
        }
    }
}

?>