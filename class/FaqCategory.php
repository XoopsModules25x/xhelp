<?php namespace Xoopsmodules\xhelp;

use Xoopsmodules\xhelp;

/**
 * class FaqCategory
 */
class FaqCategory extends \XoopsObject
{
    /**
     * xhelp\FaqCategory constructor.
     */
    public function __construct()
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('parent', XOBJ_DTYPE_INT, null, false);
    }

    /**
     * @return xhelp\FaqCategory
     */
    public function &create()
    {
        return new xhelp\FaqCategory();
    }
}
