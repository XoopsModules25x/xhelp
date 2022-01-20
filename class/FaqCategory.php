<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

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
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', \XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('parent', \XOBJ_DTYPE_INT, null, false);
    }

    /**
     * @return FaqCategory
     */
    public function &create(): FaqCategory
    {
        return new FaqCategory();
    }
}
