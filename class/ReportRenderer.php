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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

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
