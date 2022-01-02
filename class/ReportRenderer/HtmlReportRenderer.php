<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\ReportRenderer;

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
     * @param $report
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
    public function render(int $graphWidth = 500, int $graphHeight = 300)
    {
        global $paramVals;
        $report = $this->report;

        if (0 == $report->getVar('hasResults')) {
            $report->_setResults();
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
