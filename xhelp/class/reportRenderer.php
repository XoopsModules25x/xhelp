<?php
// $Id: reportRenderer.php,v 1.1 2006/02/06 19:37:59 eric_juden Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

class xhelpReportRenderer {
    var $report;
    function xhelpReportRenderer($report)
    {
        $this->report = $report;
    }

    /*
     // Not sure this function is needed

     function setData($data)
     {
     $this->data = $data;


     }
     */

    function render()
    {
        // this section should not run
    }
}

?>