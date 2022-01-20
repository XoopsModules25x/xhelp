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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       XOOPS Development Team
 */

require_once XOOPS_ROOT_PATH . '/class/xoopsform/form.php';

/**
 * class Form
 */
class Form extends \XoopsForm
{
    public $_labelWidth;

    /**
     * @param string $width
     */
    public function setLabelWidth(string $width)
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
    public function render(): string
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
            } elseif ($ele->isHidden()) {
                $ret .= $ele->render();
            } else {
                $class = 'even';

                $ret .= "<tr><td class='head' valign='top' $labelWidth><label for='" . $ele->getName(false) . "'>" . $ele->getCaption() . '</label>';
                if ('' != $ele->getDescription()) {
                    $ret .= '<br><br><span style="font-weight: normal;">' . $ele->getDescription() . '</span>';
                }
                $ret .= "</td><td class='$class'>" . $ele->render() . '</td></tr>';
            }
        }
        $ret .= "</table></form>\n";

        return $ret;
    }
}
