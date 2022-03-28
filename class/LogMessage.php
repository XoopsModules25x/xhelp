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
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
//
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * Xhelp\LogMessage class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class LogMessage extends \XoopsObject
{
    /**
     * Xhelp\LogMessage constructor.
     * @param int|array|null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('ticketid', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('lastUpdated', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('action', \XOBJ_DTYPE_TXTBOX, null, true, 255);

        if (null !== $id) {
            if (\is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * determine when the log message was updated
     *
     * @return string Timestamp of last update
     */
    public function lastUpdated(): string
    {
        return \formatTimestamp($this->getVar('lastUpdated'));
    }
}   //end of class
