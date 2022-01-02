<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * class FaqCategoryHandler
 */
class FaqCategoryHandler extends \XoopsObjectHandler
{
    /**
     * @return FaqCategory
     */
    public function &create()
    {
        return new FaqCategory();
    }
}
