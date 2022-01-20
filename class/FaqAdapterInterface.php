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
 * class FaqAdapterAbstract
 */
interface FaqAdapterInterface
{
    /**
     * Perform any initilization needed
     */
    public function init();

    /**
     * Stub function (does nothing)
     */
    public function getCategories();

    /**
     * @return bool true (success)/false (failure)
     */
    public function storeFaq(): bool;

    /**
     * @return Faq object
     */
    public function createFaq(): Faq;

    /**
     * @return bool true (success) / false (failure)
     */
    public function isActive(): bool;

    /**
     * @param \XoopsModules\Xhelp\Faq $faq
     * @return string
     */
    public function makeFaqUrl(\XoopsModules\Xhelp\Faq $faq): string;
}
