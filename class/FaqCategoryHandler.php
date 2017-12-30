<?php namespace Xoopsmodules\xhelp;

use Xoopsmodules\xhelp;

/**
 * class FaqCategoryHandler
 */
class FaqCategoryHandler extends \XoopsObjectHandler
{
    /**
     * @return xhelp\FaqCategory
     */
    public function &create()
    {
        return new xhelp\FaqCategory();
    }
}
