<?php

namespace XoopsModules\Xhelp;

require_once XOOPS_ROOT_PATH . '/class/xoopsform/form.php';

/**
 * class Form
 */
class Form extends \XoopsForm
{
    public $_labelWidth;

    /**
     * @param $width
     */
    public function setLabelWidth($width)
    {
        $this->_labelWidth = $width;
    }

    /**
     * @return mixed
     */
    public function getLabelWidth()
    {
        return $this->_labelWidth;
    }

    /**
     * create HTML to output the form as a theme-enabled table with validation.
     *
     * @return string
     */
    public function render()
    {
        $ret = "<form name='" . $this->getName() . "' id='" . $this->getName() . "' action='" . $this->getAction() . "' method='" . $this->getMethod() . "' " . $this->getExtra() . ">\n<table width='100%' class='outer' cellspacing='1'><tr><th colspan='2'>" . $this->getTitle() . '</th></tr>';

        $width = $this->getLabelWidth();
        if ($width) {
            $labelWidth = ' width="' . $width . '"';
        } else {
            $labelWidth = '';
        }
        foreach ($this->getElements() as $ele) {
            if (!\is_object($ele)) {
                $ret .= $ele;
            } elseif (!$ele->isHidden()) {
                $class = 'even';

                $ret .= "<tr><td class='head' valign='top' $labelWidth><label for='" . $ele->getName(false) . "'>" . $ele->getCaption() . '</label>';
                if ('' != $ele->getDescription()) {
                    $ret .= '<br><br><span style="font-weight: normal;">' . $ele->getDescription() . '</span>';
                }
                $ret .= "</td><td class='$class'>" . $ele->render() . '</td></tr>';
            } else {
                $ret .= $ele->render();
            }
        }
        $ret .= "</table></form>\n";

        return $ret;
    }
}
