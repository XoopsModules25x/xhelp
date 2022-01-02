<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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
     * @param bool   $extra
     * @return string
     */
    public function makeSelBox(
        $name,
        $fieldName,
        $prefix = '-',
        $selected = '',
        $addEmptyOption = false,
        $key = 0,
        $extra = false
    ) {
        $ret = '<select name="' . $name . '[]" id="' . $name . '" ' . ($extra ? 'multiple="multiple" size="6"' : '') . '>';
        if (false !== $addEmptyOption) {
            $ret .= '<option value="0"></option>';
        }
        $this->_makeSelBoxOptions($fieldName, $selected, $key, $ret, $prefix);

        return $ret . '</select>';
    }
}
