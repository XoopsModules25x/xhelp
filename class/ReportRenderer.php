<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * class ReportRenderer
 */
class ReportRenderer
{
    public $report;

    /**
     * Xhelp\ReportRenderer constructor.
     * @param $report
     */
    public function __construct($report)
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

    public function render()
    {
        // this section should not run
    }
}
