<?php namespace Xoopsmodules\xhelp;

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
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use Xoopsmodules\xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * xhelp\Notification class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class Notification extends \XoopsObject
{
    /**
     * xhelp\Notification constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('notif_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('staff_setting', XOBJ_DTYPE_INT, null, false);
        $this->initVar('user_setting', XOBJ_DTYPE_INT, null, false);
        $this->initVar('staff_options', XOBJ_DTYPE_ARRAY, null, false, 1000000);

        if (null !== $id) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}   //end of class
