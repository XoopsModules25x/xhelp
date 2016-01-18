<?php

include_once XOOPS_ROOT_PATH."/class/xoopsform/formcheckbox.php";

class xhelpFormCheckbox extends XoopsFormCheckbox
{
    /**
     * Add an option
     *
     * @param string $value
     * @param string $name
     */
    function addOption($value, $name=""){
        $this->_options[$value] = $name;
    }
}
