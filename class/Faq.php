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
 * class Faq
 */
class Faq extends \XoopsObject
{
    /**
     * Xhelp\Faq constructor.
     */
    public function __construct()
    {
        $this->initVar('subject', \XOBJ_DTYPE_TXTBOX, null, true, 100);      // Ticket subject
        $this->initVar('problem', \XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('solution', \XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('categories', \XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('url', \XOBJ_DTYPE_TXTBOX, null, true);
    }
}
