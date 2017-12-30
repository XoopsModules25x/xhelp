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
 * xhelp\Role class
 *
 * Information about an individual role
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class Role extends \XoopsObject
{
    /**
     * xhelp\Role constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 35);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false, 1024);
        $this->initVar('tasks', XOBJ_DTYPE_INT, 0, false);

        if (null !== $id) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}   // end of class
