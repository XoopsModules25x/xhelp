<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

require_once XOOPS_ROOT_PATH . '/class/tree.php';

/**
 * class Tree
 */
class Tree extends \XoopsObjectTree
{
    /**
     * @param string $name
     * @param string $fieldName
     * @param string $prefix
     * @param string $selected
     * @param bool   $addEmptyOption
     * @param int    $key
     * @param bool   $selectMulti
     * @return string
     */
    public function &makeSelBox(
        $name,
        $fieldName,
        $prefix = '-',
        $selected = '',
        $addEmptyOption = false,
        $key = 0,
        $selectMulti = false
    ) {
        $ret = '<select name="' . $name . '[]" id="' . $name . '" ' . ($selectMulti ? 'multiple="multiple" size="6"' : '') . '>';
        if (false != $addEmptyOption) {
            $ret .= '<option value="0"></option>';
        }
        $this->_makeSelBoxOptions($fieldName, $selected, $key, $ret, $prefix);

        return $ret . '</select>';
    }
}
