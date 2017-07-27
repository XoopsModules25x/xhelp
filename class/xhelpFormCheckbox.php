<?php

require_once XOOPS_ROOT_PATH . '/class/xoopsform/formcheckbox.php';

/**
 * Class XHelpFormCheckbox
 */
class XHelpFormCheckbox extends XoopsFormCheckbox
{
    /**
     * Add an option
     *
     * @param string $value
     * @param string $name
     */
    public function addOption($value, $name = '')
    {
        $this->_options[$value] = $name;
    }
}
