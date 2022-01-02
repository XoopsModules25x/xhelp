<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

require_once XOOPS_ROOT_PATH . '/class/xoopsform/formcheckbox.php';

/**
 * class FormCheckbox
 */
class FormCheckbox extends \XoopsFormCheckBox
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
