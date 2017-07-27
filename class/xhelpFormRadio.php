<?php

require_once XOOPS_ROOT_PATH . '/class/xoopsform/formradio.php';

/**
 * Class XHelpFormRadio
 */
class XHelpFormRadio extends XoopsFormRadio
{
    /**
     * Prepare HTML for output
     *
     * @return string HTML
     */
    public function render()
    {
        $ret = '';
        foreach ($this->getOptions() as $value => $name) {
            $ret      .= "<input type='radio' name='" . $this->getName() . "' id='" . $this->getName() . $value . "' value='" . $value . "'";
            $selected = $this->getValue();
            if (isset($selected) && ($value == $selected)) {
                $ret .= ' checked';
            }
            $ret .= $this->getExtra() . '>' . $name . "\n";
        }

        return $ret;
    }
}
