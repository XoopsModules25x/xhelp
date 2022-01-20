<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\ReportRenderer;

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

use XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

global $paramVals;

//Include the base reportRenderer interface (required)
// require_once XHELP_CLASS_PATH . '/ReportRenderer.php';

/**
 * class HtmlReportRenderer
 */
class HtmlReportRenderer extends Xhelp\ReportRenderer
{
    /**
     * Xhelp\HtmlReportRenderer constructor.
     * @param Report $report
     */
    public function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * @param int $graphWidth
     * @param int $graphHeight
     * @return string
     */
    public function render(int $graphWidth = 500, int $graphHeight = 300): string
    {
        global $paramVals;
        $report = $this->report;

        if (0 == $report->getVar('hasResults')) {
            $report->setResults();
        }
        $aResults = $report->getVar('results');

        $params = '';
        if (!empty($paramVals)) {
            foreach ($paramVals as $key => $value) {
                if (\is_array($value)) {
                    $params .= "&$key=$value[1]";
                } else {
                    $params .= "&$key=$value";
                }
            }
        }

        // Print graph
        $myReport = '';

        if ($report->getVar('hasGraph')) {
            $myReport .= "<div id='xhelp_graph'>";
            $myReport .= "<img src='" . \XHELP_BASE_URL . '/report.php?op=graph&name=' . $report->name . $params . "' align='center' width='" . $graphWidth . "' height='" . $graphHeight . "'>";
            $myReport .= '</div>';
        }

        // Display report
        $myReport .= '<br>';
        $myReport .= "<div id='xhelp_report'>";
        $myReport .= '<table>';
        $myReport .= '<tr>';
        $dbFields = $report->meta['dbFields'];

        // Fill in rest of report
        foreach ($dbFields as $dbField => $field) {
            $myReport .= '<th>' . $field . '</th>';
        }
        $myReport .= '</tr>';

        foreach ($dbFields as $dbField => $field) {
            ${$dbField} = '';
        }

        /*
         // Loop through each record and add it to report
         foreach ($aResults as $result) {
         $myReport .= "<tr class='even'";

         // Make blank spaces on report for repeated items
         foreach ($dbFields as $dbField=>$field) {
         if ($result[$dbField] != ${$dbField}) {
         $myReport .= "<td>".$result[$dbField]."</td>";
         ${$dbField} = $result[$dbField];
         } else {
         $myReport .= "<td></td>";
         }
         }
         $myReport .= "</tr>";
         }
         */

        foreach ($aResults as $result) {
            $myReport .= "<tr class='even'>";
            foreach ($dbFields as $dbField => $field) {
                $myReport .= '<td>' . $result[$dbField] . '</td>';
            }
            $myReport .= '</tr>';
        }

        $myReport .= '</table></div>';

        return $myReport;
    }
}
