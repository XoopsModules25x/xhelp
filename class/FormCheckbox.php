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
