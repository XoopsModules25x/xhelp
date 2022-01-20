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
 * @author       XOOPS Development Team
 */

require_once XOOPS_ROOT_PATH . '/class/tree.php';

/**
 * class Tree
 */
class Tree extends \XoopsObjectTree
{
    //    /**
    //     * @param string $name
    //     * @param string $fieldName
    //     * @param string $prefix
    //     * @param string $selected
    //     * @param bool   $addEmptyOption
    //     * @param int    $key
    //     * @param bool   $extra
    //     * @return string
    //     */
    //    public function makeSelBox(
    //        $name, $fieldName, $prefix = '-', $selected = '', $addEmptyOption = false, $key = 0, $extra = false
    //    ): string {
    //        $ret = '<select name="' . $name . '[]" id="' . $name . '" ' . ($extra ? 'multiple="multiple" size="6"' : '') . '>';
    //        if (false !== $addEmptyOption) {
    //            $ret .= '<option value="0"></option>';
    //        }
    //        $this->_makeSelBoxOptions($fieldName, $selected, $key, $ret, $prefix);
    //
    //        return $ret . '</select>';
    //    }
}
