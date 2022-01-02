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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
// require_once XHELP_CLASS_PATH . '/NaiveBayesian.php';

/**
 * Xhelp\TicketSolution class
 *
 * Represents an individual ticket solution
 *
 * @author  Brian Wahoff <brianw@epcusa.com>
 */
class TicketSolution extends \XoopsObject
{
    /**
     * Xhelp\TicketSolution constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('ticketid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('url', \XOBJ_DTYPE_TXTAREA, null, true, 4096);
        $this->initVar('title', \XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('description', \XOBJ_DTYPE_TXTAREA, null, false, 10000);
        $this->initVar('uid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('posted', \XOBJ_DTYPE_INT, null, true);

        if (null !== $id) {
            if (\is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * @param string $format
     * @return string
     */
    public function posted($format = 'l'): string
    {
        return \formatTimestamp($this->getVar('posted'), $format);
    }
}   // end of class
