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

require_once XOOPS_ROOT_PATH . '/class/xoopsform/formradio.php';

/**
 * class FormRadio
 */
class FormRadio extends \XoopsFormRadio
{
    /**
     * Prepare HTML for output
     *
     * @return string HTML
     */
    public function render(): string
    {
        $ret = '';
        foreach ($this->getOptions() as $value => $name) {
            $ret      .= "<input type='radio' name='" . $this->getName() . "' id='" . $this->getName() . $value . "' value='" . $value . "'";
            $selected = $this->getValue();
            if (null !== $selected && ($value == $selected)) {
                $ret .= ' checked';
            }
            $ret .= $this->getExtra() . '>' . $name . "\n";
        }

        return $ret;
    }
}
