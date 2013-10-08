<?php
// $Id: xhelpTree.php,v 1.4 2005/11/23 17:20:27 eric_juden Exp $
include_once XOOPS_ROOT_PATH."/class/tree.php";

class xhelpTree extends XoopsObjectTree {
    function &makeSelBox($name, $fieldName, $prefix='-', $selected='', $addEmptyOption = false, $key=0, $selectMulti=false)
    {
        $ret = '<select name="'.$name.'[]" id="'.$name.'" '. (($selectMulti) ? 'multiple="multiple" size="6"' : "") .'>';
        if (false != $addEmptyOption) {
            $ret .= '<option value="0"></option>';
        }
        $this->_makeSelBoxOptions($fieldName, $selected, $key, $ret, $prefix);
        return $ret.'</select>';
    }
}