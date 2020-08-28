<?php

namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

/**
 * class FaqCategoryHandler
 */
class FaqCategoryHandler extends \XoopsObjectHandler
{
    /**
     * @return Xhelp\FaqCategory
     */
    public function &create()
    {
        return new Xhelp\FaqCategory();
    }
}
