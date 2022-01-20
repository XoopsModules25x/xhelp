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
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * Xhelp\SavedSearch class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class SavedSearch extends \XoopsObject
{
    /**
     * Xhelp\SavedSearch constructor.
     * @param int|array|null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', \XOBJ_DTYPE_TXTBOX, null, false, 50);
        $this->initVar('search', \XOBJ_DTYPE_OTHER, null, false, 1000000);
        $this->initVar('pagenav_vars', \XOBJ_DTYPE_TXTAREA, null, false, 1000000);
        $this->initVar('hasCustFields', \XOBJ_DTYPE_INT, 0, false);

        if (null !== $id) {
            if (\is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }
}   //end of class
