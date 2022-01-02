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

/**
 * Xhelp\StaffReview class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class StaffReview extends \XoopsObject
{
    /**
     * Xhelp\StaffReview constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('staffid', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('rating', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('comments', \XOBJ_DTYPE_TXTAREA, null, false, 1024);
        $this->initVar('ticketid', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('responseid', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('submittedBy', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('userIP', \XOBJ_DTYPE_TXTBOX, null, false, 255);

        if (null !== $id) {
            if (\is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * Gets a UNIX timestamp
     *
     * @return string Timestamp of last update
     */
    public function posted(): int
    {
        return \formatTimestamp($this->getVar('updateTime'));
    }
}
