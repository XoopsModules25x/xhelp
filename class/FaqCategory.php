<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

/**
 * class FaqCategory
 */
class FaqCategory extends \XoopsObject
{
    /**
     * Xhelp\FaqCategory constructor.
     */
    public function __construct()
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('parent', XOBJ_DTYPE_INT, null, false);
    }

    /**
     * @return Xhelp\FaqCategory
     */
    public function &create()
    {
        return new Xhelp\FaqCategory();
    }
}
