<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp\Reports\Report;

if (!\defined('XHELP_CLASS_PATH')) {
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
     * @param Report $report
     */
    public function __construct(Report $report)
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
