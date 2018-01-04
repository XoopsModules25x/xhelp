<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

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
        $this->initVar('subject', XOBJ_DTYPE_TXTBOX, null, true, 100);      // Ticket subject
        $this->initVar('problem', XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('solution', XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('categories', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('url', XOBJ_DTYPE_TXTBOX, null, true);
    }
}
