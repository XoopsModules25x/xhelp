<?php

//

/**
 * Class XHelpFaqCategory
 */
class XHelpFaqCategory extends XoopsObject
{
    /**
     * XHelpFaqCategory constructor.
     */
    public function __construct()
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('parent', XOBJ_DTYPE_INT, null, false);
    }

    /**
     * @return XHelpFaqCategory
     */
    public function &create()
    {
        return new xhelpFaqCategory();
    }
}

/**
 * Class XHelpFaqCategoryHandler
 */
class XHelpFaqCategoryHandler extends XoopsObjectHandler
{
    /**
     * @return XHelpFaqCategory
     */
    public function &create()
    {
        return new xhelpFaqCategory();
    }
}
