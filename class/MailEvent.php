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
 * Xhelp\Department class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class MailEvent extends \XoopsObject
{
    /**
     * Xhelp\MailEvent constructor.
     * @param int|array|null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('mbox_id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('event_desc', \XOBJ_DTYPE_TXTAREA, null, false, 65000);
        $this->initVar('event_class', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('posted', \XOBJ_DTYPE_INT, null, false);

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
    public function posted(string $format = 'l'): string
    {
        return \formatTimestamp($this->getVar('posted'), $format);
    }
}   //end of class
