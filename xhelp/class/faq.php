<?php
// $Id: faq.php,v 1.3 2005/12/02 23:16:09 ackbarr Exp $

class xhelpFaq extends XoopsObject {
    function xhelpFaq()
    {
        $this->initVar('subject', XOBJ_DTYPE_TXTBOX, null, true, 100);      // Ticket subject
        $this->initVar('problem', XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('solution', XOBJ_DTYPE_TXTAREA, null, true);
        $this->initVar('categories', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('url', XOBJ_DTYPE_TXTBOX, null, true);
    }
}
