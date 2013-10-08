<?php

include_once XOOPS_ROOT_PATH."/class/xoopsform/formradio.php";

class xhelpFormRadio extends XoopsFormRadio
{
    /**
     * Prepare HTML for output
     *
     * @return	string	HTML
     */
    function render(){
        $ret = "";
        foreach ( $this->getOptions() as $value => $name ) {
            $ret .= "<input type='radio' name='".$this->getName()."' id='".$this->getName().$value."' value='".$value."'";
            $selected = $this->getValue();
            if ( isset($selected) && ($value == $selected) ) {
                $ret .= " checked='checked'";
            }
            $ret .= $this->getExtra()." />".$name."\n";
        }
        return $ret;
    }
}
?>