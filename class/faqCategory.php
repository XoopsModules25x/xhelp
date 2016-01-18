<?php
// $Id: faqCategory.php,v 1.3 2005/11/23 17:20:27 eric_juden Exp $

class xhelpFaqCategory extends XoopsObject {
    function xhelpFaqCategory()
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('parent', XOBJ_DTYPE_INT, null, false);
    }

    function &create()
    {
        return new xhelpFaqCategory();
    }
}

Class xhelpFaqCategoryHandler extends XoopsObjectHandler {
    function &create()
    {
        return new xhelpFaqCategory();
    }
}
