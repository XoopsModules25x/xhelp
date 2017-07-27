<?php
//

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

/**
 * Class XHelpReportRenderer
 */
class XHelpReportRenderer
{
    public $report;

    /**
     * XHelpReportRenderer constructor.
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
